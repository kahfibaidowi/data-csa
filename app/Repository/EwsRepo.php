<?php

namespace App\Repository;

use App\Models\EwsModel;
use App\Models\RegionModel;


class EwsRepo{

    public static function gets_ews_kecamatan($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['province_id']=trim($params['province_id']);
        $params['regency_id']=trim($params['regency_id']);
        $params['pulau']=trim($params['pulau']);

        //query
        $query=RegionModel::where("type", "kecamatan");
        $query=$query->with([
            "ews"   =>function($q)use($params){
                return $q->where("type", $params['type'])->where("tahun", $params['tahun']);
            },
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

    public static function gets_ews_kabupaten_kota($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['province_id']=trim($params['province_id']);
        $params['pulau']=trim($params['pulau']);

        //query
        $query=RegionModel::where("type", "kabupaten_kota");
        $query=$query->with([
            "ews"   =>function($q)use($params){
                return $q->where("type", $params['type'])->where("tahun", $params['tahun']);
            },
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