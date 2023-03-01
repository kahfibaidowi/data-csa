<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
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
        $kab_kota=RegionModel::select("id_region", "region", "nested", "type", "geo_json")
            ->where("type", "kabupaten_kota")
            ->limit(1)
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
        
        //return
        return $kab_kota;
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