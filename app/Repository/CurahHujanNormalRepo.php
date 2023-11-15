<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use App\Models\CurahHujanModel;
use App\Models\RegionModel;


class CurahHujanNormalRepo{

    public static function gets_curah_hujan_normal_treeview($params)
    {
        $provinsi=RegionModel::select("id_region", "region", "nested", "data", "type")
            ->where("type", "provinsi")
            ->get()
            ->toArray();
        $kab_kota=RegionModel::select("id_region", "region", "nested", "type")
            ->where("type", "kabupaten_kota")
            ->orderBy("nested")
            ->orderBy("region")
            ->get()
            ->toArray();
        $kecamatan=RegionModel::select("id_region", "region", "nested", "data", "type")
            ->where("type", "kecamatan")
            ->orderBy("nested")
            ->orderBy("region")
            ->get()
            ->toArray();

        $curah_hujan=DB::table("tbl_curah_hujan_normal")
            ->select("id_curah_hujan_normal", "id_region", "bulan", "input_ke", "curah_hujan_normal")
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

        //--provinsi
        $set_region=[
            'id_region' =>"-1",
            'index'     =>-1
        ];
        foreach($kab_kota as $key=>$regency){
            if(!isset($regency['kecamatan'])){
                $kab_kota[$key]['kecamatan']=[];
                $regency['kecamatan']=[];
            }

            if($regency['nested']==$set_region['id_region']){
                $provinsi[$set_region['index']]['kabupaten_kota']=array_merge($provinsi[$set_region['index']]['kabupaten_kota'], [$regency]);
            }
            else{
                $find_prov=array_find($provinsi, "id_region", $regency['nested']);
                if($find_prov!==false){
                    $provinsi[$find_prov['index']]['kabupaten_kota']=[$regency];
                    $set_region=[
                        'id_region' =>$regency['nested'],
                        'index'     =>$find_prov['index']
                    ];
                }
            }
        }
        unset($kab_kota);

        //sort
        foreach($provinsi as &$prov){
            usort($prov['kabupaten_kota'], function($a, $b){
                return strcmp(strtolower($a["region"]), strtolower($b['region']));
            });
            foreach($prov['kabupaten_kota'] as &$kab){
                usort($kab['kecamatan'], function($a, $b){
                    return strcmp(strtolower($a["region"]), strtolower($b['region']));
                });
            }
        }
        usort($provinsi, function($a, $b){
            return strcmp(strtolower($a["region"]), strtolower($b['region']));
        });
        
        //return
        return $provinsi;
    }
}