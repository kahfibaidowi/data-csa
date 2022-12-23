<?php

namespace App\Repository;

use App\Models\CurahHujanModel;
use App\Models\RegionModel;


class CurahHujanRepo{

    public static function gets_curah_hujan_kabupaten_kota($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['province_id']=trim($params['province_id']);
        $params['pulau']=trim($params['pulau']);

        //query
        $query=RegionModel::where("type", "kabupaten_kota");
        $query=$query->with([
            "curah_hujan"   =>function($q)use($params){
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
            $new_data[]=array_merge_without($val, ['geo_json', 'parent'], [
                'provinsi'  =>array_merge_without($val['parent'], ['geo_json'])
            ]);
        }

        return array_merge($data, [
            'data'  =>$new_data
        ]);
    }
}