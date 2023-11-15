<?php

namespace App\Repository;

use App\Models\SebaranOptModel;
use App\Models\RegionModel;


class SebaranOptRepo{
    
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

        //return
        return $sebaran->toArray();
    }

    public static function gets_region_kabupaten_kota($params)
    {
        //params
        $params['pulau']=isset($params['pulau'])?trim($params['pulau']):"";
        $params['province_id']=isset($params['province_id'])?trim($params['province_id']):"";

        //query
        $query=RegionModel::select("id_region", "nested", "region");
        $query=$query->with("parent:id_region,nested,region,data");
        $query=$query->where("type", "kabupaten_kota");
        //pulau
        if($params['pulau']!=""){
            $query=$query->whereHas("parent", function($q)use($params){
                $q->where("data->pulau", $params['pulau']);
            });
        }
        //province id
        if($params['province_id']!=""){
            $query=$query->where("nested", $params['province_id']);
        }
        //--order
        $query=$query->orderBy("region");
        
        //return
        return $query->get()->toArray();
    }
}