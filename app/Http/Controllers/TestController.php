<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\RegionModel;
use App\Repository\FrontpageRepo;
use App\Models\SebaranOptModel;

class TestController extends Controller
{
    public function gets_json(Request $request)
    {
        $req=$request->all();

        if(true){
            return response()->json([
                'status'=>"not allowed"
            ]);
        }

        //SUCCESS
        $json=file_get_contents("http://localhost/latihan/geojson/kabkota.geojson");
        $json=json_decode($json, true);

        $kabkota=RegionModel::where("type", "kabupaten_kota")->get();

        $data=[];
        foreach($kabkota as $obj){
            $arr=$this->find_data($json['features'], ['properties', 'WADMKK'], $obj['region']);

            if($arr===false){
                echo "tidak ada ".$obj['region']."<br/>";
            }
            else{
                $data[]=array_merge($arr, [
                    'properties'=>array_merge($arr['properties'], [
                        'id_region' =>$obj['id_region']
                    ])
                ]);
            }
        }

        return response()->json($data);

        // $data=[];
        // foreach($json['features'] as $obj){
        //     $arr=$this->find_data($kabkota, ['region'], $obj['properties']['WADMKK']);

        //     if($arr===false){
        //         $data[]=$obj['properties'];
        //     }
        // }

        // return response()->json($data);

    }

    private function find_data($array, $key, $value){
        $value=str_replace(" ", "", strtoupper($value));

        foreach($array as $element){
            $ek=str_replace(" ", "", strtoupper($element[$key[0]][$key[1]]));
            if($ek==$value){
                return $element;
            }
        }
        return false;
    }

    public function update(Request $request){
        $req=$request->all();

        if(true){
            return response()->json([
                'status'=>"not allowed"
            ]);
        }

        //SUCCESS
        $json=file_get_contents("http://localhost/data-csa/public/kabkota.geojson");
        $json=json_decode($json, true);

        foreach($json['features'] as $obj){
            $region=RegionModel::where("id_region", $obj['properties']['id_region'])->lockForUpdate()->first();

            $region->update([
                'geo_json'  =>array_merge($region['geo_json'], [
                    'graph' =>$obj['geometry']
                ])
            ]);
        }
    }

    public function test_paginate(Request $request){
        $req=$request->all();

        if(true){
            return response()->json([
                'status'=>"not allowed"
            ]);
        }

        $data=DB::table("tbl_region")->select("region")->where("type", "kabupaten_kota")->orderBy("region")->paginate(10);

        return response()->json(['data'=>$data]);
    }

    public function update_center(Request $request){
        $req=$request->all();

        if(true){
            return response()->json([
                'status'=>"not allowed"
            ]);
        }

        //SUCCESS
        $json=file_get_contents("http://localhost/data-csa/public/center.json");
        $json=json_decode($json, true);

        foreach($json as $v){
            $region=RegionModel::where("id_region", $v['id_region'])->lockForUpdate()->first();
            
            $region->update([
                'geo_json'  =>array_merge($region['geo_json'], [
                    'map_center'=>[
                        'latitude'  =>$v['center'][1],
                        'longitude' =>$v['center'][0],
                        'zoom'      =>10
                    ]
                ])
            ]);
        }
    }

    public function get_center(Request $request){
        $req=$request->all();

        if(true){
            return response()->json([
                'status'=>"not allowed"
            ]);
        }

        //SUCCESS
        $v=RegionModel::select("geo_json->map_center as map_center")->where("id_region", "2")->first();
        $v['map_center']=json_decode($v['map_center'], true);
        echo $v['map_center']['latitude'];
    }

    public function import_sebaran_opt(Request $request){
        $req=$request->all();

        if(true){
            return response()->json([
                'status'=>"not allowed"
            ]);
        }

        //SUCCESS
        $json=file_get_contents("http://localhost/latihan/data_opt.json");
        $json=json_decode($json, true);

        foreach($json as $v){
            SebaranOptModel::create([
                'bulan'     =>$v['bulan'],
                'tahun'     =>$v['tahun'],
                'provinsi'  =>$v['provinsi'],
                'kab_kota'  =>$v['kab_kota'],
                'opt'       =>$v['opt'],
                'komoditas' =>$v['komoditas'],
                'lts_berat' =>is_numeric($v['lts_berat'])?$v['lts_berat']:0,
                'lts_puso'  =>is_numeric($v['lts_puso'])?$v['lts_puso']:0,
                'lts_ringan'=>is_numeric($v['lts_ringan'])?$v['lts_ringan']:0,
                'lts_sedang'=>is_numeric($v['lts_sedang'])?$v['lts_sedang']:0,
                'sum_lts'   =>is_numeric($v['sum_total_lts'])?$v['sum_total_lts']:0
            ]);
        }
    }

    public function update_sebaran_opt(Request $request){
        $req=$request->all();

        if(false){
            return response()->json([
                'status'=>"not allowed"
            ]);
        }

        $json=file_get_contents("http://localhost/latihan/tbl_region.json");
        $json=json_decode($json, true);

        $reg=$json[2]['data'];
        $new_reg=[];
        foreach($reg as $r){
            if($r['type']=="kabupaten_kota"){
                $new_reg[]=$r;
            }
        }

        //return response()->json(['data'=>$new_reg]);

        function search_region($array, $str){
            foreach ($array as $key=>$val) {
                if($val['region']===$str){
                    return $key;
                }
            }
            return -1;
        }

        $sebaran=SebaranOptModel::where("id_region", null)->limit(100000)->get();
        $region=$new_reg;

        foreach($region as &$reg){
            $reg['region']=str_replace(" ", "", $reg['region']);
            $reg['region']=str_replace("-", "", $reg['region']);
        }

        $count_not_found=0;
        $not_found=[];
        foreach($sebaran as &$seb){
            $replace=str_replace("Kabupaten ", "", $seb['kab_kota']);
            $replace=str_replace(" ", "", $replace);
            $replace=str_replace("-", "", $replace);
            $upper=strtoupper($replace);

            $key=search_region($region, $upper);
            if($key==-1){
                $count_not_found++;
                $not_found[]=$seb;
            }
            else{
                SebaranOptModel::where("id_sebaran_opt", $seb['id_sebaran_opt'])
                    ->update([
                        'id_region' =>$region[$key]['id_region']
                    ]);
            }
            
        }

        echo "<table border='1' style='border-collapse:collapse'>";
        foreach($not_found as $seb){
            echo "<tr>";
            echo "<td>".$seb['kab_kota']."</td>";
            echo "<td></td>";
            echo "</tr>";
        }
        echo "</table>";

        // return response()->json([
        //     'not_found' =>$count_not_found,
        //     'sebaran'   =>[]
        // ]);

        // return response()->json([
        //     'data'  =>$sebaran,
        //     'not_found' =>$not_found,
        //     'count' =>$count_not_found
        // ]);
    }
}
