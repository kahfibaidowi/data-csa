<?php

namespace App\Repository;

use App\Models\EwsModel;
use App\Models\RegionModel;


class EwsRepo{

    public static function gets_ews_kabupaten_kota($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $query=RegionModel::where("type", "provinsi");
        $query=$query->with([
            //--q & kabupaten kota
            "kabupaten_kota"=>function($q)use($params){
                return $q->where("region", "like", "%".$params['q']."%");
            },
            //--ews
            "kabupaten_kota.ews"=>function($q)use($params){
                return $q->where("type", $params['type'])->where("tahun", $params['tahun']);
            }
        ]);
        //--order
        $query=$query->orderBy("region");

        //return
        $data=$query->paginate($params['per_page'])->toArray();

        $new_data=[];
        foreach($data['data'] as $val){
            $kabupaten_kota=[];
            foreach($val['kabupaten_kota'] as $val2){
                $kabupaten_kota[]=array_merge_without($val2, ['geo_json']);
            }

            if(count($kabupaten_kota)>0){
                $new_data[]=array_merge_without($val, ['geo_json'], [
                    'kabupaten_kota'=>$kabupaten_kota
                ]);
            }
        }

        return array_merge($data, [
            'data'  =>$new_data
        ]);
    }
}