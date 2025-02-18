<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\CurahHujanModel;
use App\Models\RegionModel;
use App\Models\EwsModel;
use App\Models\FrontpageModel;
use App\Models\SebaranOptModel;
use App\Models\BantuanDPIModel;


class FrontpageRepo{

    public static function get_summary_ews_produksi($params)
    {
        //query
        $query=EwsModel::select(\DB::raw("coalesce(sum(produksi), 0) as total_produksi"), "type")
            ->where("tahun", $params['tahun'])
            ->groupBy("type");

        //return
        return $query->get();
    }

    public static function get_summary_sifat_hujan_kabupaten_kota($params)
    {
        //QUERY
        $kab_kota=RegionModel::with("parent:region,id_region,nested")->select("id_region", "region", "nested", "type", "geo_json")
            ->where("type", "kabupaten_kota")
            ->get()
            ->toArray();

        $kecamatan=RegionModel::with("parent:region,id_region,nested", "parent.parent:region,id_region,nested")->select("id_region", "region", "nested", "data", "geo_json", "type")
            ->where("type", "kecamatan")
            ->orderBy("nested")
            ->orderBy("region")
            ->get()
            ->toArray();
        $new_kecamatan=[];
        foreach($kecamatan as $val){
            $new_kecamatan[]=array_merge($val, [
                'geo_json'  =>[
                    'map_center'=>$val['geo_json']['map_center']
                ]
            ]);
        }
        $kecamatan=$new_kecamatan;

        $curah_hujan=DB::table("tbl_curah_hujan as a")
            ->join("tbl_curah_hujan_normal as b", function($join){
                $join->on("a.id_region", "=", "b.id_region");
                $join->on("a.bulan", "=", "b.bulan");
                $join->on("a.input_ke", "=", "b.input_ke");
            })
            ->select("a.id_curah_hujan", "a.id_region", "a.tahun", "a.bulan", "a.input_ke", "a.curah_hujan", "b.curah_hujan_normal")
            ->where("a.tahun", $params['tahun'])
            ->orderBy("a.id_region")
            ->get();
        $curah_hujan=json_decode(json_encode($curah_hujan), true);

        //process
        //--curah hujan
        $set_region=[
            'id_region' =>"-1",
            'index'     =>-1
        ];
        foreach($curah_hujan as $ch){
            if($ch['id_region']==$set_region['id_region']){
                $kecamatan[$set_region['index']]['curah_hujan']=array_merge($kecamatan[$set_region['index']]['curah_hujan'], [$ch]);
            }
            else{
                $find_kecamatan=array_find($kecamatan, "id_region", $ch['id_region']);
                if($find_kecamatan!==false){
                    $kecamatan[$find_kecamatan['index']]['curah_hujan']=[$ch];
                    $set_region=[
                        'id_region' =>$ch['id_region'],
                        'index'     =>$find_kecamatan['index']
                    ];
                }
            }
        }
        unset($curah_hujan);
        
        //--kabupaten
        $set_region=[
            'id_region' =>"-1",
            'index'     =>-1
        ];
        foreach($kecamatan as $key=>$kec){
            if(!isset($kec['curah_hujan'])){
                $kecamatan[$key]['curah_hujan']=[];
                $kec['curah_hujan']=[];  
            }

            if($kec['nested']==$set_region['id_region']){
                $kab_kota[$set_region['index']]['kecamatan']=array_merge($kab_kota[$set_region['index']]['kecamatan'], [$kec]);
            }
            else{
                $find_kab_kota=array_find($kab_kota, "id_region", $kec['nested']);
                if($find_kab_kota!==false){
                    $kab_kota[$find_kab_kota['index']]['kecamatan']=[$kec];
                    $set_region=[
                        'id_region' =>$kec['nested'],
                        'index'     =>$find_kab_kota['index']
                    ];
                }
            }
        }
        unset($kecamatan);
        
        //return
        return $kab_kota;
    }

    // public static function get_summary_sifat_hujan_kecamatan($params)
    // {
    //     //query
    //     $query=RegionModel::with("parent:id_region,nested,region")->where("type", "kecamatan");
    //     //--curah hujan
    //     $query=$query->with([
    //         "curah_hujan"   =>function($q)use($params){
    //             return $q->where("tahun", $params['tahun']);
    //         }
    //     ]);
    //     //--order
    //     $query=$query->orderBy("region");

    //     //return
    //     return $query->get();
    // }
    
    public static function get_jadwal_tanam_kecamatan($params)
    {
        //PARAMS
        $params['per_page']=!empty($params['per_page'])?trim($params['per_page']):env("DB_ROW_LIMIT");
        $params['q']=isset($params['q'])?trim($params['q']):"";

        //QUERY
        //--kecamatan
        $q_kecamatan=DB::table("tbl_region as a")
            ->join("tbl_region as b", "a.nested", "=", "b.id_region")
            ->join("tbl_region as c", "b.nested", "=", "c.id_region")
            ->selectRaw("a.id_region, a.region, a.nested, a.data, a.type, b.region as kabupaten_kota, c.region as provinsi")
            ->where("a.type", "kecamatan")
            ->where("a.region", "like", "%".$params['q']."%");
        $kecamatan=$q_kecamatan
            ->orderBy("a.region")
            ->paginate($params['per_page'])
            ->toArray();
        $kecamatan=json_decode(json_encode($kecamatan), true);
        $kecamatan_in=[-1];
        foreach($kecamatan['data'] as &$val){
            $val['data']=json_decode($val['data'], true);
            $kecamatan_in[]=$val['id_region'];
        }

        //--curah hujan
        $q_curah_hujan=DB::table("tbl_curah_hujan as ch")
            ->join("tbl_region as a", "ch.id_region", "=", "a.id_region")
            ->join("tbl_curah_hujan_normal as chn", function($join){
                $join->on("ch.id_region", "=", "chn.id_region");
                $join->on("ch.bulan", "=", "chn.bulan");
                $join->on("ch.input_ke", "=", "chn.input_ke");
            })
            ->select("ch.id_curah_hujan", "ch.id_region", "ch.tahun", "ch.bulan", "ch.input_ke", "ch.curah_hujan", "chn.curah_hujan_normal")
            ->where("ch.tahun", $params['tahun'])
            // METHOD 1
            // ->where("a.region", "like", "%".$params['q']."%");
            // METHOD 2
            ->whereIn("a.id_region", $kecamatan_in);
        $curah_hujan=$q_curah_hujan
            ->orderBy("ch.id_region")
            ->get();
        $curah_hujan=json_decode(json_encode($curah_hujan), true);

        //process
        //--curah hujan
        $set_region=[
            'id_region' =>"-1",
            'index'     =>-1
        ];
        foreach($curah_hujan as $ch){
            if($ch['id_region']==$set_region['id_region']){
                $kecamatan['data'][$set_region['index']]['curah_hujan']=array_merge($kecamatan['data'][$set_region['index']]['curah_hujan'], [$ch]);
            }
            else{
                $find_kecamatan=array_find($kecamatan['data'], "id_region", $ch['id_region']);
                if($find_kecamatan!==false){
                    $kecamatan['data'][$find_kecamatan['index']]['curah_hujan']=[$ch];
                    $set_region=[
                        'id_region' =>$ch['id_region'],
                        'index'     =>$find_kecamatan['index']
                    ];
                }
            }
        }
        unset($curah_hujan);
        
        //kecamatan
        foreach($kecamatan['data'] as &$kec){
            if(!isset($kec['curah_hujan'])){
                $kec['curah_hujan']=[];
            }
        }
        
        //return
        return $kecamatan;
    }

    public static function gets_region_provinsi($params)
    {
        //query
        $query=RegionModel::select("id_region", "nested", "region", "data")->where("type", "provinsi");
        //--order
        $query=$query->orderBy("region");

        //return
        return $query->get();
    }

    public static function gets_region_kabupaten_kota($params)
    {
        //params
        $params['province_id']=isset($params['province_id'])?trim($params['province_id']):"";

        //query
        $query=RegionModel::select("id_region", "nested", "region", "data", "geo_json")->where("type", "kabupaten_kota");
        //--province
        if($params['province_id']!=""){
            $query=$query->where("nested", $params['province_id']);
        }
        //--order
        $query=$query->orderBy("region");

        //data
        $data=$query->get()->toArray();

        $new_data=[];
        foreach($data as $val){
            $new_data[]=array_merge($val, [
                'geo_json'=>[
                    'map_center'=>$val['geo_json']['map_center']
                ]
            ]);
        }

        return $new_data;
    }

    public static function gets_region_kecamatan($params)
    {
        //params
        $params['regency_id']=isset($params['regency_id'])?trim($params['regency_id']):"";

        //query
        $query=RegionModel::select("id_region", "nested", "region", "data", "geo_json")->where("type", "kecamatan");
        //--regency
        if($params['regency_id']!=""){
            $query=$query->where("nested", $params['regency_id']);
        }
        //--order
        $query=$query->orderBy("region");

        //data
        $data=$query->get()->toArray();

        $new_data=[];
        foreach($data as $val){
            $new_data[]=array_merge($val, [
                'geo_json'=>[
                    'map_center'=>$val['geo_json']['map_center']
                ]
            ]);
        }

        return $new_data;
    }

    public static function gets_sebaran_opt($params)
    {
        //params
        $params['per_page']=isset($params['per_page'])?trim($params['per_page']):"";
        $params['komoditas']=isset($params['komoditas'])?trim($params['komoditas']):"";
        $params['tahun']=isset($params['tahun'])?trim($params['tahun']):"";
        $params['bulan']=isset($params['bulan'])?trim($params['bulan']):"";
        $params['province_id']=isset($params['province_id'])?trim($params['province_id']):"";
        $params['regency_id']=isset($params['regency_id'])?trim($params['regency_id']):"";

        //query
        //--sebaran opt
        $q_sebaran=SebaranOptModel::with("region:id_region,nested,region", "region.parent:id_region,nested,region");
        if($params['province_id']!=""){
            $q_sebaran=$q_sebaran->whereHas("region", function($q)use($params){
                $q->where("nested", $params['province_id']);
            });
        }
        if($params['regency_id']!=""){
            $q_sebaran=$q_sebaran->where("id_region", $params['regency_id']);
        }
        if($params['komoditas']!=""){
            $q_sebaran=$q_sebaran->where("komoditas", $params['komoditas']);
        }
        if($params['tahun']!=""){
            $q_sebaran=$q_sebaran->where("tahun", $params['tahun']);
        }
        if($params['bulan']!=""){
            $q_sebaran=$q_sebaran->where("bulan", $params['bulan']);
        }
        $sebaran=$q_sebaran->paginate($params['per_page']);

        //--infografis
        $q_infografis=SebaranOptModel::query();
        $q_infografis->selectRaw("ifnull(sum(lts_ringan), 0) as sum_lts_ringan, 
                                    ifnull(sum(lts_sedang), 0) as sum_lts_sedang, 
                                    ifnull(sum(lts_berat), 0) as sum_lts_berat, 
                                    ifnull(sum(sum_lts), 0) as sum_sum_lts, 
                                    ifnull(sum(lts_puso), 0) as sum_lts_puso,
                                    ifnull(sum(lks_ringan), 0) as sum_lks_ringan, 
                                    ifnull(sum(lks_sedang), 0) as sum_lks_sedang, 
                                    ifnull(sum(lks_berat), 0) as sum_lks_berat, 
                                    ifnull(sum(sum_lks), 0) as sum_sum_lks, 
                                    ifnull(sum(lks_puso), 0) as sum_lks_puso,
                                    ifnull(sum(lp_pemusnahan), 0) as sum_lp_pemusnahan, 
                                    ifnull(sum(lp_pestisida_kimia), 0) as sum_lp_pestisida_kimia, 
                                    ifnull(sum(lp_cara_lain), 0) as sum_lp_cara_lain, 
                                    ifnull(sum(sum_lp), 0) as sum_sum_lp, 
                                    ifnull(sum(lp_agens_hayati), 0) as sum_lp_agens_hayati");
        if($params['komoditas']!=""){
            $q_infografis=$q_infografis->where("komoditas", $params['komoditas']);
        }
        if($params['tahun']!=""){
            $q_infografis=$q_infografis->where("tahun", $params['tahun']);
        }
        if($params['bulan']!=""){
            $q_infografis=$q_infografis->where("bulan", $params['bulan']);
        }
        if($params['province_id']!=""){
            $q_infografis=$q_infografis->whereHas("region", function($q)use($params){
                $q->where("nested", $params['province_id']);
            });
        }
        if($params['regency_id']!=""){
            $q_infografis=$q_infografis->where("id_region", $params['regency_id']);
        }
        $infografis=$q_infografis->first();

        //return
        return [
            'data'      =>$sebaran->toArray(),
            'infografis'=>$infografis->toArray()
        ];
    }

    public static function gets_sebaran_region()
    {
        $provinsi=RegionModel::select("id_region", "region")->where("type", "provinsi");
        $kab_kota=RegionModel::select("id_region", "nested", "region")->where("type", "kabupaten_kota");

        return [
            'provinsi'  =>$provinsi->get(),
            'kab_kota'  =>$kab_kota->get()
        ];
    }

    public static function gets_region_sebaran_opt($params)
    {
        //params
        $params['per_page']=isset($params['per_page'])?trim($params['per_page']):"";
        $params['komoditas']=isset($params['komoditas'])?trim($params['komoditas']):"";
        $params['tahun']=isset($params['tahun'])?trim($params['tahun']):"";
        $params['bulan']=isset($params['bulan'])?trim($params['bulan']):"";
        $params['province_id']=isset($params['province_id'])?trim($params['province_id']):"";


        $q_region=RegionModel::where("type", "kabupaten_kota");
        $q_region=$q_region->with([
            "parent:id_region,nested,region",
            "sebaran_opt"   =>function($q)use($params){
                if($params['komoditas']!=""){
                    $q->where("komoditas", $params['komoditas']);
                }
                if($params['tahun']!=""){
                    $q->where("tahun", $params['tahun']);
                }
                if($params['bulan']!=""){
                    $q->where("bulan", $params['bulan']);
                }
            }
        ]);
        if($params['province_id']!=""){
            $q_region=$q_region->where("nested", $params['province_id']);
        }
        $sebaran=$q_region->paginate($params['per_page']);

        return $sebaran->toArray();
    }
    
    // public static function gets_curah_hujan_kecamatan($params)
    // {
    //     //params
    //     $params['regency_id']=isset($params['regency_id'])?trim($params['regency_id']):"";


    //     $kecamatan=RegionModel::select("id_region", "region", "nested", "data", "geo_json", "type")
    //         ->where("type", "kecamatan");
    //     //--regency
    //     if($params['regency_id']!=""){
    //         $kecamatan=$kecamatan->where("nested", $params['regency_id']);
    //     }
    //     $kecamatan=$kecamatan->orderBy("nested")
    //         ->orderBy("region")
    //         ->get()
    //         ->toArray();

    //     $curah_hujan=DB::table("tbl_curah_hujan")
    //         ->select("id_curah_hujan", "id_region", "tahun", "bulan", "input_ke", "curah_hujan", "curah_hujan_normal")
    //         ->where("tahun", $params['tahun'])
    //         ->orderBy("id_region")
    //         ->get();
    //     $curah_hujan=json_decode(json_encode($curah_hujan), true);

    //     //process
    //     //--curah hujan
    //     $set_region=[
    //         'id_region' =>"-1",
    //         'index'     =>-1
    //     ];
    //     foreach($curah_hujan as $ch){
    //         if($ch['id_region']==$set_region['id_region']){
    //             $kecamatan[$set_region['index']]['curah_hujan']=array_merge($kecamatan[$set_region['index']]['curah_hujan'], [$ch]);
    //         }
    //         else{
    //             $find_kecamatan=array_find($kecamatan, "id_region", $ch['id_region']);
    //             if($find_kecamatan!==false){
    //                 $kecamatan[$find_kecamatan['index']]['curah_hujan']=[$ch];
    //                 $set_region=[
    //                     'id_region' =>$ch['id_region'],
    //                     'index'     =>$find_kecamatan['index']
    //                 ];
    //             }
    //         }
    //     }
    //     unset($curah_hujan);

    //     //--kecamatan
    //     foreach($kecamatan as &$kec){
    //         if(!isset($kec['curah_hujan'])){
    //             $kec['curah_hujan']=[];
    //         }
    //     }
        
    //     //return
    //     return $kecamatan;
    // }

    public static function gets_geojson_curah_hujan_kecamatan_copy($params)
    {
        //params
        $params['regency_id']=isset($params['regency_id'])?trim($params['regency_id']):"";


        //|kecamatan
        $kecamatan=DB::table("tbl_region as a")
            ->select("a.id_region", "a.region", "a.nested", "a.data", "a.geo_json", "a.type", "b.id_region as id_region_kabupaten_kota", "c.id_region as id_region_provinsi", "b.region as kabupaten_kota", "c.region as provinsi")
            ->join("tbl_region as b", "a.nested", "=", "b.id_region")
            ->join("tbl_region as c", "b.nested", "=", "c.id_region")
            ->where("a.type", "kecamatan");
        //--regency
        if($params['regency_id']!=""){
            $kecamatan=$kecamatan->where("a.nested", $params['regency_id']);
        }
        $kecamatan=$kecamatan
            ->orderBy("a.region")
            ->get();
        $kecamatan=json_decode(json_encode($kecamatan), true);

        //|curah hujan
        $curah_hujan=DB::table("tbl_curah_hujan as a")
            ->join("tbl_region as b", "a.id_region", "=", "b.id_region")
            ->select("a.id_region", "a.tahun", "a.bulan", "a.input_ke", "a.curah_hujan", "a.curah_hujan_normal");
        //--regency
        if($params['regency_id']!=""){
            $curah_hujan=$curah_hujan->where("b.nested", $params['regency_id']);
        }
        $curah_hujan=$curah_hujan
            ->orderBy("a.id_region")
            ->get();
        $curah_hujan=json_decode(json_encode($curah_hujan), true);

        //process
        //--kecamatan
        $features=[];
        foreach($kecamatan as $kec){
            $kec['geo_json']=json_decode($kec['geo_json'], true);
            
            $features[]=[
                'type'      =>"Feature",
                'properties'=>[
                    'id_region'     =>$kec['id_region'],
                    'region'        =>$kec['region'],
                    'kabupaten_kota'=>$kec['kabupaten_kota'],
                    'provinsi'      =>$kec['provinsi'],
                    'curah_hujan'   =>[],
                    'map_center'    =>$kec['geo_json']['map_center']
                ],
                'geometry'  =>isset($kec['geo_json']['graph'])?$kec['geo_json']['graph']:['type'=>"MultiPolygon", 'coordinates'=>[]]
            ];
        }
        unset($kecamatan);

        //--curah hujan
        $set_region=[
            'id_region' =>"-1",
            'index'     =>-1
        ];
        foreach($curah_hujan as $ch){
            $ch_generated=$ch['tahun']."|".$ch['bulan']."|".$ch['input_ke']."|".$ch['curah_hujan']."|".$ch['curah_hujan_normal'];
            if($ch['id_region']==$set_region['id_region']){
                $features[$set_region['index']]['properties']['curah_hujan']=array_merge($features[$set_region['index']]['properties']['curah_hujan'], [$ch_generated]);
            }
            else{
                $find_kecamatan=array_find_properties($features, "id_region", $ch['id_region']);
                if($find_kecamatan!==false){
                    $features[$find_kecamatan['index']]['properties']['curah_hujan']=[$ch_generated];
                    $set_region=[
                        'id_region' =>$ch['id_region'],
                        'index'     =>$find_kecamatan['index']
                    ];
                }
            }
        }
        unset($curah_hujan);
        
        //return
        $geojson=[
            'type'      =>"FeatureCollection",
            'name'      =>"curah_hujan_kecamatan",
            'features'  =>$features
        ];

        return $geojson;
    }

    public static function gets_geojson_curah_hujan_kecamatan($params)
    {
        //params
        $params['regency_id']=isset($params['regency_id'])?trim($params['regency_id']):"";


        //|kecamatan
        $kecamatan=DB::table("tbl_region as a")
            ->select("a.id_region", "a.region", "a.nested", "a.data", "a.geo_json", "a.type", "b.id_region as id_region_kabupaten_kota", "c.id_region as id_region_provinsi", "b.region as kabupaten_kota", "c.region as provinsi")
            ->join("tbl_region as b", "a.nested", "=", "b.id_region")
            ->join("tbl_region as c", "b.nested", "=", "c.id_region")
            ->where("a.type", "kecamatan");
        //--regency
        if($params['regency_id']!=""){
            $kecamatan=$kecamatan->where("a.nested", $params['regency_id']);
        }
        $kecamatan=$kecamatan
            ->orderBy("a.region")
            ->get();
        $kecamatan=json_decode(json_encode($kecamatan), true);

        //|curah hujan
        $curah_hujan=DB::table("tbl_curah_hujan as a")
            ->join("tbl_region as b", "a.id_region", "=", "b.id_region")
            ->select("a.id_region", "a.tahun", "a.bulan", "a.input_ke", "a.curah_hujan");
        //--regency
        if($params['regency_id']!=""){
            $curah_hujan=$curah_hujan->where("b.nested", $params['regency_id']);
        }
        $curah_hujan=$curah_hujan
            ->orderBy("a.id_region")
            ->get();
        $curah_hujan=json_decode(json_encode($curah_hujan), true);

        //|curah hujan normal
        $curah_hujan_normal=DB::table("tbl_curah_hujan_normal as a")
            ->join("tbl_region as b", "a.id_region", "=", "b.id_region")
            ->select("a.id_region", "a.bulan", "a.input_ke", "a.curah_hujan_normal");
        $curah_hujan_normal=$curah_hujan_normal
            ->orderBy("a.id_region")
            ->get();
        $curah_hujan_normal=json_decode(json_encode($curah_hujan_normal), true);

        //process
        //--kecamatan
        $features=[];
        foreach($kecamatan as $kec){
            $kec['geo_json']=json_decode($kec['geo_json'], true);
            
            $features[]=[
                'type'      =>"Feature",
                'properties'=>[
                    'id_region'     =>$kec['id_region'],
                    'region'        =>$kec['region'],
                    'kabupaten_kota'=>$kec['kabupaten_kota'],
                    'provinsi'      =>$kec['provinsi'],
                    'curah_hujan'   =>[],
                    'curah_hujan_normal'=>[],
                    'map_center'    =>$kec['geo_json']['map_center']
                ],
                'geometry'  =>isset($kec['geo_json']['graph'])?$kec['geo_json']['graph']:['type'=>"MultiPolygon", 'coordinates'=>[]]
            ];
        }
        unset($kecamatan);

        //--curah hujan normal
        $set_region=[
            'id_region' =>"-1",
            'index'     =>-1
        ];
        foreach($curah_hujan_normal as $ch){
            $ch_generated=$ch['bulan']."|".$ch['input_ke']."|".$ch['curah_hujan_normal'];
            if($ch['id_region']==$set_region['id_region']){
                $features[$set_region['index']]['properties']['curah_hujan_normal']=array_merge($features[$set_region['index']]['properties']['curah_hujan_normal'], [$ch_generated]);
            }
            else{
                $find_kecamatan=array_find_properties($features, "id_region", $ch['id_region']);
                if($find_kecamatan!==false){
                    $features[$find_kecamatan['index']]['properties']['curah_hujan_normal']=[$ch_generated];
                    $set_region=[
                        'id_region' =>$ch['id_region'],
                        'index'     =>$find_kecamatan['index']
                    ];
                }
            }
        }
        unset($curah_hujan_normal);

        //--curah hujan
        $set_region=[
            'id_region' =>"-1",
            'index'     =>-1
        ];
        foreach($curah_hujan as $ch){
            $ch_generated=$ch['tahun']."|".$ch['bulan']."|".$ch['input_ke']."|".$ch['curah_hujan'];
            if($ch['id_region']==$set_region['id_region']){
                $features[$set_region['index']]['properties']['curah_hujan']=array_merge($features[$set_region['index']]['properties']['curah_hujan'], [$ch_generated]);
            }
            else{
                $find_kecamatan=array_find_properties($features, "id_region", $ch['id_region']);
                if($find_kecamatan!==false){
                    $features[$find_kecamatan['index']]['properties']['curah_hujan']=[$ch_generated];
                    $set_region=[
                        'id_region' =>$ch['id_region'],
                        'index'     =>$find_kecamatan['index']
                    ];
                }
            }
        }
        unset($curah_hujan);
        
        //return
        $geojson=[
            'type'      =>"FeatureCollection",
            'name'      =>"curah_hujan_kecamatan",
            'features'  =>$features
        ];

        return $geojson;
    }

    public static function gets_bantuan_dpi($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['tahun']=trim($params['tahun']);
        $params['province_id']=trim($params['province_id']);
        $params['regency_id']=trim($params['regency_id']);
        $params['district_id']=trim($params['district_id']);

        //query
        $query=BantuanDPIModel::with(
            "region:id_region,region,nested", 
            "region.parent:id_region,region,nested", 
            "region.parent.parent:id_region,region,nested"
        );
        //--q
        $query=$query->where(function($q)use($params){
            $q->where("jenis_bantuan", "like", "%".$params['q']."%")
                ->orWhere("kelompok_tani", "like", "%".$params['q']."%")
                ->orWhere("pj_kelompok_tani", "like", "%".$params['q']."%");
        });
        //--region
        if($params['province_id']!=""){
            $query=$query->whereHas("region.parent.parent", function($q)use($params){
                $q->where("id_region", $params['province_id']);
            });
        }
        if($params['regency_id']!=""){
            $query=$query->whereHas("region.parent", function($q)use($params){
                $q->where("id_region", $params['regency_id']);
            });
        }
        if($params['district_id']!=""){
            $query=$query->where("id_region", $params['district_id']);
        }
        //--tahun
        if($params['tahun']!=""){
            $query=$query->where("tahun", $params['tahun']);
        }

        //--order
        $query=$query->orderByDesc("id_bantuan_dpi");

        //return
        return $query->paginate($params['per_page'])->toArray();
    }

    public static function gets_bantuan_dpi_region()
    {
        $provinsi=RegionModel::select("id_region", "region")->where("type", "provinsi");
        $kab_kota=RegionModel::select("id_region", "nested", "region")->where("type", "kabupaten_kota");
        $kecamatan=RegionModel::select("id_region", "nested", "region")->where("type", "kecamatan");

        return [
            'provinsi'  =>$provinsi->get(),
            'kab_kota'  =>$kab_kota->get(),
            'kecamatan' =>$kecamatan->get()
        ];
    }

    public static function gets_bantuan_dpi_peta()
    {
        $query=RegionModel::select("id_region" ,"region", "nested", "geo_json->map_center as map_center");
        $query=$query->with("parent:id_region,region,nested", "parent.parent:id_region,region,nested");
        $query=$query->withWhereHas("bantuan_dpi");
        $query=$query->where("type", "kecamatan");

        return $query->get();
    }

    //REGION
    public static function gets_all_region()
    {
        $provinsi=RegionModel::select("id_region", "region")->where("type", "provinsi");
        $kab_kota=RegionModel::select("id_region", "nested", "region")->where("type", "kabupaten_kota");
        $kecamatan=RegionModel::select("id_region", "nested", "region")->where("type", "kecamatan");

        return [
            'provinsi'  =>$provinsi->get(),
            'kab_kota'  =>$kab_kota->get(),
            'kecamatan' =>$kecamatan->get()
        ];
    }

    //CURAH HUJAN
    public static function gets_curah_hujan($params)
    {
        $params['per_page']=isset($params['per_page'])?trim($params['per_page']):"";
        $params['id_region']=isset($params['id_region'])?trim($params['id_region']):"";
        $params['tahun']=isset($params['tahun'])?trim($params['tahun']):"";

        //query
        $query=CurahHujanModel::with("ch_normal");
        //--id_region
        if($params['id_region']!=""){
            $query=$query->where("id_region", $params['id_region']);
        }
        //--tahun
        if($params['tahun']!=""){
            $query=$query->where("tahun", $params['tahun']);
        }

        $query=$query->orderBy("id_region");
        $query=$query->orderBy("tahun");
        $query=$query->orderBy("bulan");
        $query=$query->orderBy("input_ke");

        //return
        $data=$query->paginate($params['per_page'])->toArray();

        $curah_hujan=[];
        foreach($data['data'] as $val){
            $curah_hujan[]=array_merge($val, [
                'curah_hujan_normal'=>$val['ch_normal']['curah_hujan_normal'],
                'ch_normal'         =>null
            ]);
        }

        return array_merge($data, [
            'data'  =>$curah_hujan
        ]);
    }

    //ADMIN
    public static function get_widget($type)
    {
        //query
        $query=FrontpageModel::where("type", $type);

        return $query->first()->toArray();
    }
    public static function get_post($post_id)
    {
        //query
        $query=FrontpageModel::where("type", "post")->where('id_frontpage', $post_id);

        return $query->first()->toArray();
    }
    public static function gets_post($params)
    {
        //params
        $params['q']=isset($params['q'])?trim($params['q']):"";
        $params['per_page']=isset($params['per_page'])?trim($params['per_page']):"";

        //query
        $data=FrontpageModel::
            where("type", "post")
            ->where("data->title", "like", "%".$params['q']."%")
            ->orderByDesc("id_frontpage")
            ->paginate($params['per_page'])
            ->toArray();

        return $data;
    }
}