<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use App\Models\CurahHujanModel;
use App\Models\CurahHujanActivityModel;
use App\Models\RegionModel;


class CurahHujanRepo{

    // public static function gets_curah_hujan_kecamatan($params)
    // {
    //     //params
    //     $params['per_page']=trim($params['per_page']);
    //     $params['province_id']=trim($params['province_id']);
    //     $params['regency_id']=trim($params['regency_id']);
    //     $params['pulau']=trim($params['pulau']);

    //     //query
    //     $query=RegionModel::where("type", "kecamatan");
    //     $query=$query->with([
    //         "curah_hujan"   =>function($q)use($params){
    //             return $q->where("tahun", $params['tahun']);
    //         },
    //         "parent",
    //         "parent.parent"
    //     ]);
    //     $query=$query->where("region", "like", "%".$params['q']."%");
    //     //parent regency
    //     $query=$query->whereHas("parent", function($q)use($params){
    //         //--regency id
    //         if($params['regency_id']!=""){
    //             $q=$q->where("id_region", $params['regency_id']);
    //         }
    //     });
    //     //parent province
    //     $query=$query->whereHas("parent.parent", function($q)use($params){
    //         //--province id
    //         if($params['province_id']!=""){
    //             $q=$q->where("id_region", $params['province_id']);
    //         }
    //         if($params['pulau']!=""){
    //             $q=$q->where("data->pulau", $params['pulau']);
    //         }
    //     });
    //     //--order
    //     $query=$query->orderBy("region");

    //     //return
    //     $data=$query->paginate($params['per_page'])->toArray();
    //     return $data;
        
    //     $new_data=[];
    //     foreach($data['data'] as $val){
    //         $new_data[]=array_merge_without($val, ['geo_json', 'parent'], [
    //             'provinsi'      =>array_merge_without($val['parent']['parent'], ['geo_json']),
    //             'kabupaten_kota'=>array_merge_without($val['parent'], ['geo_json', 'parent'])
    //         ]);
    //     }

    //     return array_merge($data, [
    //         'data'  =>$new_data
    //     ]);
    // }

    // public static function gets_curah_hujan_kabupaten_kota($params)
    // {
    //     //params
    //     $params['per_page']=trim($params['per_page']);
    //     $params['province_id']=trim($params['province_id']);
    //     $params['pulau']=trim($params['pulau']);

    //     //query
    //     $query=RegionModel::where("type", "kabupaten_kota");
    //     $query=$query->with([
    //         "curah_hujan_kabupaten_kota"=>function($q)use($params){
    //             return $q->where("tahun", $params['tahun']);
    //         },
    //         "parent"
    //     ]);
    //     $query=$query->where("region", "like", "%".$params['q']."%");
    //     //parent
    //     $query=$query->whereHas("parent", function($q)use($params){
    //         //--province id
    //         if($params['province_id']!=""){
    //             $q=$q->where("id_region", $params['province_id']);
    //         }
    //         if($params['pulau']!=""){
    //             $q=$q->where("data->pulau", $params['pulau']);
    //         }
    //     });
    //     //--order
    //     $query=$query->orderBy("region");

    //     //return
    //     $data=$query->paginate($params['per_page'])->toArray();

    //     $new_data=[];
    //     foreach($data['data'] as $val){
    //         $curah_hujan=[];
    //         for($i=1; $i<=12; $i++){
    //             for($j=1; $j<=3; $j++){
    //                 //curah hujan
    //                 $filter_ch=array_filter($val['curah_hujan_kabupaten_kota'], function($obj)use($i, $j){
    //                     return (strval($obj['bulan'])==strval($i) && strval($obj['input_ke'])==strval($j));
    //                 });

    //                 if(count($filter_ch)>0){
    //                     $sum_curah_hujan=array_reduce($filter_ch, function($carry, $item){
    //                         return $carry+=doubleval($item['curah_hujan']);
    //                     }, 0);
    //                     $sum_curah_hujan_normal=array_reduce($filter_ch, function($carry, $item){
    //                         return $carry+=doubleval($item['curah_hujan_normal']);
    //                     }, 0);

    //                     $ch=[
    //                         'tahun'     =>$params['tahun'],
    //                         'bulan'     =>$i,
    //                         'input_ke'  =>$j,
    //                         'curah_hujan'       =>$sum_curah_hujan/count($filter_ch),
    //                         'curah_hujan_normal'=>$sum_curah_hujan_normal/count($filter_ch)
    //                     ];
    //                     $curah_hujan[]=$ch;
    //                 }
    //             }
    //         }

    //         $new_data[]=array_merge_without($val, ['geo_json', 'parent', 'curah_hujan_kabupaten_kota'], [
    //             'curah_hujan'   =>$curah_hujan,
    //             'provinsi'      =>array_merge_without($val['parent'], ['geo_json'])
    //         ]);
    //     }

    //     return array_merge($data, [
    //         'data'  =>$new_data
    //     ]);
    // }

    // public static function gets_curah_hujan_provinsi($params)
    // {
    //     //params
    //     $params['per_page']=trim($params['per_page']);
    //     $params['pulau']=trim($params['pulau']);

    //     //query
    //     $query=RegionModel::where("type", "provinsi");
    //     $query=$query->with([
    //         "curah_hujan_provinsi"  =>function($q)use($params){
    //             return $q->where("tahun", $params['tahun']);
    //         }
    //     ]);
    //     $query=$query->where("region", "like", "%".$params['q']."%");
    //     //--pulau
    //     if($params['pulau']!=""){
    //         $query=$query->where("data->pulau", $params['pulau']);
    //     }
    //     //--order
    //     $query=$query->orderBy("region");

    //     //return
    //     $data=$query->paginate($params['per_page'])->toArray();

    //     $new_data=[];
    //     foreach($data['data'] as $val){
    //         $curah_hujan=[];
    //         for($i=1; $i<=12; $i++){
    //             for($j=1; $j<=3; $j++){
    //                 //curah hujan
    //                 $filter_ch=array_filter($val['curah_hujan_provinsi'], function($obj)use($i, $j){
    //                     return (strval($obj['bulan'])==strval($i) && strval($obj['input_ke'])==strval($j));
    //                 });

    //                 if(count($filter_ch)>0){
    //                     $sum_curah_hujan=array_reduce($filter_ch, function($carry, $item){
    //                         return $carry+=doubleval($item['curah_hujan']);
    //                     }, 0);
    //                     $sum_curah_hujan_normal=array_reduce($filter_ch, function($carry, $item){
    //                         return $carry+=doubleval($item['curah_hujan_normal']);
    //                     }, 0);

    //                     $ch=[
    //                         'tahun'     =>$params['tahun'],
    //                         'bulan'     =>$i,
    //                         'input_ke'  =>$j,
    //                         'curah_hujan'       =>$sum_curah_hujan/count($filter_ch),
    //                         'curah_hujan_normal'=>$sum_curah_hujan_normal/count($filter_ch)
    //                     ];
    //                     $curah_hujan[]=$ch;
    //                 }
    //             }
    //         }

    //         $new_data[]=array_merge_without($val, ['geo_json', 'curah_hujan_provinsi'], [
    //             'curah_hujan'   =>$curah_hujan
    //         ]);
    //     }

    //     return array_merge($data, [
    //         'data'  =>$new_data
    //     ]);
    // }

    public static function gets_curah_hujan_treeview($params)
    {
        $provinsi=RegionModel::select("id_region", "region", "nested", "data", "type")
            ->where("type", "provinsi")
            ->get()
            ->toArray();
        $kab_kota=RegionModel::select("id_region", "region", "nested", "type")
            ->where("type", "kabupaten_kota")
            ->orderBy("nested")
            ->orderBy("region")
            ->get()
            ->toArray();
        $kecamatan=RegionModel::select("id_region", "region", "nested", "data", "type")
            ->where("type", "kecamatan")
            ->orderBy("nested")
            ->orderBy("region")
            ->get()
            ->toArray();

        // $curah_hujan=DB::table("tbl_curah_hujan as a")
        //     ->rightJoin("tbl_curah_hujan_normal as b", function($join){
        //         $join->on("a.id_region", "=", "b.id_region");
        //         $join->on("a.bulan", "=", "b.bulan");
        //         $join->on("a.input_ke", "=", "b.input_ke");
        //     })
        //     ->select("a.id_curah_hujan", "a.id_region", "a.tahun", "a.bulan", "a.input_ke", "a.curah_hujan", DB::raw("coalesce(b.curah_hujan_normal, '') as curah_hujan_normal"))
        //     ->where("a.tahun", $params['tahun'])
        //     ->orderBy("a.id_region")
        //     ->get();
        // $curah_hujan=json_decode(json_encode($curah_hujan), true);
        $curah_hujan=DB::table("tbl_curah_hujan_normal as a")
            ->leftJoin("tbl_curah_hujan as b", function($join)use($params){
                $join->on("a.id_region", "=", "b.id_region");
                $join->on("a.bulan", "=", "b.bulan");
                $join->on("a.input_ke", "=", "b.input_ke");
                $join->on("b.tahun", DB::raw($params['tahun']));
            })
            ->select("b.id_curah_hujan", "a.id_region", DB::raw($params['tahun']." as tahun"), "a.bulan", "a.input_ke", DB::raw("coalesce(b.curah_hujan, '') as curah_hujan"), "a.curah_hujan_normal")
            ->orderBy("a.id_region")
            ->get();
        $curah_hujan=json_decode(json_encode($curah_hujan), true);

        // return response()->json(['curah_hujan'=>$curah_hujan]);

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

        //--provinsi
        $set_region=[
            'id_region' =>"-1",
            'index'     =>-1
        ];
        foreach($kab_kota as $key=>$regency){
            if(!isset($regency['kecamatan'])){
                $kab_kota[$key]['kecamatan']=[];
                $regency['kecamatan']=[];
            }
            
            if($regency['nested']==$set_region['id_region']){
                $provinsi[$set_region['index']]['kabupaten_kota']=array_merge($provinsi[$set_region['index']]['kabupaten_kota'], [$regency]);
            }
            else{
                $find_prov=array_find($provinsi, "id_region", $regency['nested']);
                if($find_prov!==false){
                    $provinsi[$find_prov['index']]['kabupaten_kota']=[$regency];
                    $set_region=[
                        'id_region' =>$regency['nested'],
                        'index'     =>$find_prov['index']
                    ];
                }
            }
        }
        unset($kab_kota);

        //sort
        foreach($provinsi as &$prov){
            usort($prov['kabupaten_kota'], function($a, $b){
                return strcmp(strtolower($a["region"]), strtolower($b['region']));
            });
            foreach($prov['kabupaten_kota'] as &$kab){
                usort($kab['kecamatan'], function($a, $b){
                    return strcmp(strtolower($a["region"]), strtolower($b['region']));
                });
            }
        }
        usort($provinsi, function($a, $b){
            return strcmp(strtolower($a["region"]), strtolower($b['region']));
        });
        
        //return
        return $provinsi;
    }

    // public static function gets_activity($params)
    // {
    //     //PARAMS
    //     $params['per_page']=isset($params['per_page'])?trim($params['per_page']):"";
    //     $params['q']=isset($params['q'])?trim($params['q']):"";
    //     $params['tahun']=isset($params['tahun'])?trim($params['tahun']):"";
    //     $params['province_id']=isset($params['province_id'])?trim($params['province_id']):"";
    //     $params['regency_id']=isset($params['regency_id'])?trim($params['regency_id']):"";

    //     //REGION
    //     $q_region=RegionModel::with("parent:id_region,region,nested", "parent.parent:id_region,region,nested");
    //     $q_region=$q_region->where("type", "kecamatan")
    //         ->where("region", "like", "%".$params['q']."%");
    //     //--province id
    //     if($params['province_id']!=""){
    //         $q_region=$q_region->whereHas("parent", function($q)use($params){
    //             $q->where("nested", $params['province_id']);
    //         });
    //     }
    //     //--regency id
    //     if($params['regency_id']!=""){
    //         $q_region=$q_region->where("nested", $params['regency_id']);
    //     }
    //     //--order
    //     $q_region=$q_region->orderBy("region");
    //     $region=$q_region->paginate($params['per_page'])->toArray();

    //     //ACTIVITY
    //     $q_activity=CurahHujanActivityModel::with("user:id_user,nama_lengkap");
    //     $q_activity=$q_activity->where("tahun", $params['tahun']);
    //     //--province id
    //     if($params['province_id']!=""){
    //         $q_activity=$q_activity->whereHas("region.parent", function($q)use($params){
    //             $q->where("nested", $params['province_id']);
    //         });
    //     }
    //     //--regency id
    //     if($params['regency_id']!=""){
    //         $q_activity=$q_activity->whereHas("region", function($q)use($params){
    //             $q->where("nested", $params['regency_id']);
    //         });
    //     }
    //     $q_activity=$q_activity->orderBy("id_region");
    //     $q_activity=$q_activity->orderBy("id_curah_hujan_activity");
    //     $activity=$q_activity->get()->toArray();

    //     //PROCESS
    //     $new_region=[];
    //     foreach($region['data'] as $val){
    //         $new_region[]=array_merge($val, [
    //             'activity'  =>[]
    //         ]);
    //     }

    //     $set_region=[
    //         'id_region' =>"-1",
    //         'index'     =>-1
    //     ];
    //     foreach($activity as $val){
    //         if($val['id_region']==$set_region['id_region']){
    //             $new_region[$set_region['index']]['activity']=array_merge($new_region[$set_region['index']]['activity'], [$val]);
    //         }
    //         else{
    //             $find_region=array_find($new_region, "id_region", $val['id_region']);
    //             if($find_region!==false){
    //                 $new_region[$find_region['index']]['activity']=[$val];
    //                 $set_region=[
    //                     'id_region' =>$val['id_region'],
    //                     'index'     =>$find_region['index']
    //                 ];
    //             }
    //         }
    //     }
        
    //     //RETURN
    //     return array_merge($region, [
    //         'data'  =>$new_region
    //     ]);
    // }

    public static function gets_activity($params)
    {
        //PARAMS
        $params['per_page']=isset($params['per_page'])?trim($params['per_page']):"";
        $params['q']=isset($params['q'])?trim($params['q']):"";
        $params['tahun']=isset($params['tahun'])?trim($params['tahun']):"";
        $params['province_id']=isset($params['province_id'])?trim($params['province_id']):"";
        $params['regency_id']=isset($params['regency_id'])?trim($params['regency_id']):"";

        //REGION
        $q_region=RegionModel::select("id_region", "nested", "type", "region", "data", "created_at", "updated_at");
        $q_region=$q_region->with("parent:id_region,region,nested", "parent.parent:id_region,region,nested");
        $q_region=$q_region->with("curah_hujan_activity.user:id_user,nama_lengkap");
        $q_region=$q_region->with("curah_hujan_activity", function($q)use($params){
            return $q->where("tahun", $params['tahun']);
        });
        $q_region=$q_region->where("type", "kecamatan")
            ->where("region", "like", "%".$params['q']."%");
        //--province id
        if($params['province_id']!=""){
            $q_region=$q_region->whereHas("parent", function($q)use($params){
                $q->where("nested", $params['province_id']);
            });
        }
        //--regency id
        if($params['regency_id']!=""){
            $q_region=$q_region->where("nested", $params['regency_id']);
        }
        //--order
        $q_region=$q_region->orderBy("region");

        //RETURN
        return $q_region->paginate($params['per_page'])->toArray();
    }
}