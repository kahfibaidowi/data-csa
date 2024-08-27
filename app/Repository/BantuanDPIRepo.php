<?php

namespace App\Repository;

use App\Models\BantuanDPIModel;


class BantuanDPIRepo{
    
    public static function get($opt_id)
    {
        //query
        $query=BantuanDPIModel::where("id_bantuan_dpi", $opt_id);
        
        //return
        return optional($query->first())->toArray();
    }

    public static function gets($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);
        $params['tahun']=trim($params['tahun']);
        $params['province_id']=trim($params['province_id']);
        $params['regency_id']=trim($params['regency_id']);
        $params['district_id']=trim($params['district_id']);

        //query
        $query=BantuanDPIModel::with("region:id_region,region,nested", "region.parent:id_region,region,nested", "region.parent.parent:id_region,region,nested");
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
}