<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\RegionModel;
use App\Repository\FrontpageRepo;

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
}
