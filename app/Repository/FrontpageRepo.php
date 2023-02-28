<?php

namespace App\Repository;

use App\Models\CurahHujanModel;
use App\Models\RegionModel;
use App\Models\EwsModel;


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
        //query
        $query=RegionModel::with(
                "parent:id_region,nested,region", 
                "kecamatan:id_region,nested,region", 
                "kecamatan.curah_hujan:id_curah_hujan,id_region,tahun,bulan,input_ke,curah_hujan,curah_hujan_normal"
            )->where("type", "kabupaten_kota");
        //--curah hujan
        $query=$query->with([
            "kecamatan.curah_hujan" =>function($q)use($params){
                return $q->where("tahun", $params['tahun']);
            }
        ]);
        //--order
        $query=$query->orderBy("region");

        //return
        return $query->get();
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

    public static function gets_region_provinsi($params)
    {
        //query
        $query=RegionModel::select("id_region", "nested", "region", "data")->where("type", "provinsi");
        //--order
        $query=$query->orderBy("region");

        //return
        return $query->get();
    }
}