<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use App\Models\CurahHujanModel;
use App\Models\RegionModel;


class CurahHujanRepo{

    public static function gets_curah_hujan_kecamatan($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['province_id']=trim($params['province_id']);
        $params['regency_id']=trim($params['regency_id']);
        $params['pulau']=trim($params['pulau']);

        //query
        $query=RegionModel::where("type", "kecamatan");
        $query=$query->with([
            "curah_hujan"   =>function($q)use($params){
                return $q->where("tahun", $params['tahun']);
            },
            "parent",
            "parent.parent"
        ]);
        $query=$query->where("region", "like", "%".$params['q']."%");
        //parent regency
        $query=$query->whereHas("parent", function($q)use($params){
            //--regency id
            if($params['regency_id']!=""){
                $q=$q->where("id_region", $params['regency_id']);
            }
        });
        //parent province
        $query=$query->whereHas("parent.parent", function($q)use($params){
            //--province id
            if($params['province_id']!=""){
                $q=$q->where("id_region", $params['province_id']);
            }
            if($params['pulau']!=""){
                $q=$q->where("data->pulau", $params['pulau']);
            }
        });
        //--order
        $query=$query->orderBy("region");

        //return
        $data=$query->paginate($params['per_page'])->toArray();
        return $data;
        
        $new_data=[];
        foreach($data['data'] as $val){
            $new_data[]=array_merge_without($val, ['geo_json', 'parent'], [
                'provinsi'      =>array_merge_without($val['parent']['parent'], ['geo_json']),
                'kabupaten_kota'=>array_merge_without($val['parent'], ['geo_json', 'parent'])
            ]);
        }

        return array_merge($data, [
            'data'  =>$new_data
        ]);
    }

    public static function gets_curah_hujan_kabupaten_kota($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['province_id']=trim($params['province_id']);
        $params['pulau']=trim($params['pulau']);

        //query
        $query=RegionModel::where("type", "kabupaten_kota");
        $query=$query->with([
            "curah_hujan_kabupaten_kota"=>function($q)use($params){
                return $q->where("tahun", $params['tahun']);
            },
            "parent"
        ]);
        $query=$query->where("region", "like", "%".$params['q']."%");
        //parent
        $query=$query->whereHas("parent", function($q)use($params){
            //--province id
            if($params['province_id']!=""){
                $q=$q->where("id_region", $params['province_id']);
            }
            if($params['pulau']!=""){
                $q=$q->where("data->pulau", $params['pulau']);
            }
        });
        //--order
        $query=$query->orderBy("region");

        //return
        $data=$query->paginate($params['per_page'])->toArray();

        $new_data=[];
        foreach($data['data'] as $val){
            $curah_hujan=[];
            for($i=1; $i<=12; $i++){
                for($j=1; $j<=3; $j++){
                    //curah hujan
                    $filter_ch=array_filter($val['curah_hujan_kabupaten_kota'], function($obj)use($i, $j){
                        return (strval($obj['bulan'])==strval($i) && strval($obj['input_ke'])==strval($j));
                    });

                    if(count($filter_ch)>0){
                        $sum_curah_hujan=array_reduce($filter_ch, function($carry, $item){
                            return $carry+=doubleval($item['curah_hujan']);
                        }, 0);
                        $sum_curah_hujan_normal=array_reduce($filter_ch, function($carry, $item){
                            return $carry+=doubleval($item['curah_hujan_normal']);
                        }, 0);

                        $ch=[
                            'tahun'     =>$params['tahun'],
                            'bulan'     =>$i,
                            'input_ke'  =>$j,
                            'curah_hujan'       =>$sum_curah_hujan/count($filter_ch),
                            'curah_hujan_normal'=>$sum_curah_hujan_normal/count($filter_ch)
                        ];
                        $curah_hujan[]=$ch;
                    }
                }
            }

            $new_data[]=array_merge_without($val, ['geo_json', 'parent', 'curah_hujan_kabupaten_kota'], [
                'curah_hujan'   =>$curah_hujan,
                'provinsi'      =>array_merge_without($val['parent'], ['geo_json'])
            ]);
        }

        return array_merge($data, [
            'data'  =>$new_data
        ]);
    }

    public static function gets_curah_hujan_provinsi($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['pulau']=trim($params['pulau']);

        //query
        $query=RegionModel::where("type", "provinsi");
        $query=$query->with([
            "curah_hujan_provinsi"  =>function($q)use($params){
                return $q->where("tahun", $params['tahun']);
            }
        ]);
        $query=$query->where("region", "like", "%".$params['q']."%");
        //--pulau
        if($params['pulau']!=""){
            $query=$query->where("data->pulau", $params['pulau']);
        }
        //--order
        $query=$query->orderBy("region");

        //return
        $data=$query->paginate($params['per_page'])->toArray();

        $new_data=[];
        foreach($data['data'] as $val){
            $curah_hujan=[];
            for($i=1; $i<=12; $i++){
                for($j=1; $j<=3; $j++){
                    //curah hujan
                    $filter_ch=array_filter($val['curah_hujan_provinsi'], function($obj)use($i, $j){
                        return (strval($obj['bulan'])==strval($i) && strval($obj['input_ke'])==strval($j));
                    });

                    if(count($filter_ch)>0){
                        $sum_curah_hujan=array_reduce($filter_ch, function($carry, $item){
                            return $carry+=doubleval($item['curah_hujan']);
                        }, 0);
                        $sum_curah_hujan_normal=array_reduce($filter_ch, function($carry, $item){
                            return $carry+=doubleval($item['curah_hujan_normal']);
                        }, 0);

                        $ch=[
                            'tahun'     =>$params['tahun'],
                            'bulan'     =>$i,
                            'input_ke'  =>$j,
                            'curah_hujan'       =>$sum_curah_hujan/count($filter_ch),
                            'curah_hujan_normal'=>$sum_curah_hujan_normal/count($filter_ch)
                        ];
                        $curah_hujan[]=$ch;
                    }
                }
            }

            $new_data[]=array_merge_without($val, ['geo_json', 'curah_hujan_provinsi'], [
                'curah_hujan'   =>$curah_hujan
            ]);
        }

        return array_merge($data, [
            'data'  =>$new_data
        ]);
    }

    public static function gets_curah_hujan_treeview($params)
    {
        $provinsi=RegionModel::select("id_region", "region", "nested", "data", "type")
            ->where("type", "provinsi")
            ->get()
            ->toArray();
        $kab_kota=RegionModel::select("id_region", "region", "nested", "type")
            ->where("type", "kabupaten_kota")
            ->get()
            ->toArray();
        $kecamatan=RegionModel::select("id_region", "region", "nested", "data", "type")
            ->where("type", "kecamatan")
            ->get()
            ->toArray();

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

        //--provinsi
        $set_region=[
            'id_region' =>"-1",
            'index'     =>-1
        ];
        foreach($kab_kota as $key=>$regency){
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
}