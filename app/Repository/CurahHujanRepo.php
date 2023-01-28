<?php

namespace App\Repository;

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
        //query
        $query=RegionModel::where("type", "provinsi");
        $query=$query->with([
            "kabupaten_kota",
            "kabupaten_kota.kecamatan",
            "kabupaten_kota.kecamatan.curah_hujan"=>function($q)use($params){
                return $q->where("tahun", $params['tahun']);
            },
        ]);
        $query=$query->orderBy("region");

        //return
        $data=$query->paginate()->toArray();

        $new_data=[];
        foreach($data['data'] as $val){
            $kab_kota=[];
            foreach($val['kabupaten_kota'] as $regency){
                $kecamatan=[];
                foreach($regency['kecamatan'] as $district){
                    $kecamatan[]=array_merge_without($district, ['geo_json']);
                }

                $kab_kota[]=array_merge_without($regency, ['geo_json'], [
                    'kecamatan' =>$kecamatan
                ]);
            }
            $new_data[]=array_merge_without($val, ['geo_json'], [
                'kabupaten_kota'=>$kab_kota
            ]);
        }

        return array_merge($data, [
            'data'  =>$new_data
        ]);
    }
}