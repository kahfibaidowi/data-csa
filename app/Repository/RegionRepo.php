<?php

namespace App\Repository;

use App\Models\RegionModel;


class RegionRepo{
    
    public static function gets_pulau($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $query=RegionModel::select("data->pulau as pulau");
        $query=$query->where("type", "provinsi")
            ->where("data->pulau", "like", "%".$params['q']."%")
            ->where("data->pulau", "!=", "")
            ->groupBy("pulau")
            ->orderBy("data->pulau");
        
        //return
        return $query->paginate($params['per_page'])->toArray();
    }

    public static function gets_provinsi($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['pulau']=trim($params['pulau']);

        //query
        $query=RegionModel::query();
        $query=$query->where("type", "provinsi")->where("region", "like", "%".$params['q']."%");
        //--pulau
        if($params['pulau']!=""){
            $query=$query->where("data->pulau", $params['pulau']);
        }
        //--order
        $query=$query->orderBy("region");
        
        //return
        return $query->paginate($params['per_page'])->toArray();
    }

    public static function gets_kabupaten_kota($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['province_id']=trim($params['province_id']);

        //query
        $query=RegionModel::query();
        $query=$query->where("type", "kabupaten_kota")
            ->where("region", "like", "%".$params['q']."%");
        //province id
        if($params['province_id']!=""){
            $query=$query->where("nested", $params['province_id']);
        }
        //--order
        $query=$query->orderBy("region");
        
        //return
        return $query->paginate($params['per_page'])->toArray();
    }

    public static function gets_kecamatan($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['province_id']=trim($params['province_id']);
        $params['regency_id']=trim($params['regency_id']);

        //query
        $query=RegionModel::with("parent:id_region,region,nested");
        $query=$query->where("type", "kecamatan")
            ->where("region", "like", "%".$params['q']."%");
        //--province id
        if($params['province_id']!=""){
            $query=$query->whereHas("parent", function($q)use($params){
                $q->where("nested", $params['province_id']);
            });
        }
        //--regency id
        if($params['regency_id']!=""){
            $query=$query->where("nested", $params['regency_id']);
        }
        //--order
        $query=$query->orderBy("region");
        
        //return
        $data=$query->paginate($params['per_page'])->toArray();
        $new_data=[];
        foreach($data['data'] as $val){
            $new_data[]=array_merge_without($val, ['parent'], [
                'kabupaten_kota'=>$val['parent']
            ]);
        }

        return array_merge($data, [
            'data'  =>$new_data
        ]);
    }

    public static function get_region($region_id)
    {
        //query
        $query=RegionModel::where("id_region", $region_id);

        //return
        return optional($query->first())->toArray();
    }
}