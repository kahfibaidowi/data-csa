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
        $query=RegionModel::where("type", "kabupaten_kota");
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
}