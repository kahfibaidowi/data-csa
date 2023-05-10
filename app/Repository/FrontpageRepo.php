<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use App\Models\CurahHujanModel;
use App\Models\RegionModel;
use App\Models\EwsModel;
use App\Models\FrontpageModel;
use App\Models\SebaranOptModel;


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

        $curah_hujan=DB::table("tbl_curah_hujan")
            ->select("id_curah_hujan", "id_region", "tahun", "bulan", "input_ke", "curah_hujan", "curah_hujan_normal")
            ->where("tahun", $params['tahun'])
            ->orderBy("id_region")
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

    public static function get_summary_sifat_hujan_kecamatan($params)
    {
        //query
        $query=RegionModel::with("parent:id_region,nested,region")->where("type", "kecamatan");
        //--curah hujan
        $query=$query->with([
            "curah_hujan"   =>function($q)use($params){
                return $q->where("tahun", $params['tahun']);
            }
        ]);
        //--order
        $query=$query->orderBy("region");

        //return
        return $query->get();
    }
    
    public static function get_jadwal_tanam_kecamatan($params)
    {
        //PARAMS
        $params['per_page']=isset($params['per_page'])?trim($params['per_page']):env("DB_ROW_LIMIT");
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
        foreach($kecamatan['data'] as &$val){
            $val['data']=json_decode($val['data'], true);
        }

        //--curah hujan
        $q_curah_hujan=DB::table("tbl_curah_hujan as ch")
            ->join("tbl_region as a", "ch.id_region", "=", "a.id_region")
            ->select("ch.id_curah_hujan", "ch.id_region", "ch.tahun", "ch.bulan", "ch.input_ke", "ch.curah_hujan", "ch.curah_hujan_normal")
            ->where("ch.tahun", $params['tahun'])
            ->where("a.region", "like", "%".$params['q']."%");
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

    public static function gets_sebaran_opt($params)
    {
        //params
        $params['per_page']=isset($params['per_page'])?trim($params['per_page']):"";
        $params['komoditas']=isset($params['komoditas'])?trim($params['komoditas']):"";
        $params['tahun']=isset($params['tahun'])?trim($params['tahun']):"";
        $params['bulan']=isset($params['bulan'])?trim($params['bulan']):"";
        $params['provinsi']=isset($params['provinsi'])?trim($params['provinsi']):"";
        $params['kab_kota']=isset($params['kab_kota'])?trim($params['kab_kota']):"";

        //query
        //--sebaran opt
        $q_sebaran=SebaranOptModel::query();
        if($params['komoditas']!=""){
            $q_sebaran=$q_sebaran->where("komoditas", $params['komoditas']);
        }
        if($params['tahun']!=""){
            $q_sebaran=$q_sebaran->where("tahun", $params['tahun']);
        }
        if($params['bulan']!=""){
            $q_sebaran=$q_sebaran->where("bulan", $params['bulan']);
        }
        if($params['provinsi']!=""){
            $q_sebaran=$q_sebaran->where("provinsi", $params['provinsi']);
        }
        if($params['kab_kota']!=""){
            $q_sebaran=$q_sebaran->where("kab_kota", $params['kab_kota']);
        }
        $sebaran=$q_sebaran->paginate($params['per_page']);

        //--infografis
        $q_infografis=SebaranOptModel::query();
        $q_infografis->selectRaw("ifnull(sum(lts_ringan), 0) as sum_lts_ringan, 
                                    ifnull(sum(lts_sedang), 0) as sum_lts_sedang, 
                                    ifnull(sum(lts_berat), 0) as sum_lts_berat, 
                                    ifnull(sum(sum_lts), 0) as sum_sum_lts, 
                                    ifnull(sum(lts_puso), 0) as sum_lts_puso");
        if($params['komoditas']!=""){
            $q_infografis=$q_infografis->where("komoditas", $params['komoditas']);
        }
        if($params['tahun']!=""){
            $q_infografis=$q_infografis->where("tahun", $params['tahun']);
        }
        if($params['bulan']!=""){
            $q_infografis=$q_infografis->where("bulan", $params['bulan']);
        }
        if($params['provinsi']!=""){
            $q_infografis=$q_infografis->where("provinsi", $params['provinsi']);
        }
        if($params['kab_kota']!=""){
            $q_infografis=$q_infografis->where("kab_kota", $params['kab_kota']);
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
        $provinsi=SebaranOptModel::select("provinsi")->groupBy("provinsi");
        $kab_kota=SebaranOptModel::select("provinsi", "kab_kota")->groupBy("provinsi", "kab_kota");

        return [
            'provinsi'  =>$provinsi->get(),
            'kab_kota'  =>$kab_kota->get()
        ];
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