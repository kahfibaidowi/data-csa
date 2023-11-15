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
use App\Models\CurahHujanModel;
use App\Models\CurahHujanNormalModel;

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

        if(true){
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

    public function update_center_kecamatan(Request $request){
        $req=$request->all();

        if(true){
            return response()->json([
                'status'=>"not allowed"
            ]);
        }

        //SUCCESS
        $json=file_get_contents("http://localhost/data-csa/public/kecamatan_centers.json");
        $json=json_decode($json, true);

        foreach($json['data'] as $v){
            $region=RegionModel::where("id_region", $v['id_region'])->lockForUpdate()->first();
            
            $region->update([
                'geo_json'  =>array_merge($region['geo_json'], [
                    'map_center'=>[
                        'latitude'  =>$v['center']['latitude'],
                        'longitude' =>$v['center']['longitude'],
                        'zoom'      =>10
                    ]
                ])
            ]);
        }
    }

    public function validation(Request $request){
        // $customMessages=[];
        // $customMessages['array.max'] = 'Array cant have more :max items';

        // foreach ($request->get('array') as $key => $value) {
        //     $customMessages['array.' . $key . '.jaja.min'] = 'Baris '.($key+1).' kolom jaja cant be greater :min charackters';
        // }

        // $this->validate($request, [
        //     'array' => 'required|array|max:3',
        //     'array.*' => 'required',
        //     'array.*.jaja'=>"required|min:100"
        // ], $customMessages);

        // $curah_hujan=DB::table("tbl_curah_hujan as a")
        //     ->leftJoin("tbl_curah_hujan_normal as b", function($join){
        //         $join->on("a.id_region", "=", "b.id_region");
        //         $join->on("a.bulan", "=", "b.bulan");
        //         $join->on("a.input_ke", "=", "b.input_ke");
        //     })
        //     ->select("a.id_curah_hujan", "a.id_region", "a.tahun", "a.bulan", "a.input_ke", "a.curah_hujan", "b.curah_hujan_normal")
        //     ->where("a.tahun", "2023")
        //     ->orderBy("id_region")
        //     ->limit(10)
        //     ->get();
        // $curah_hujan=json_decode(json_encode($curah_hujan), true);

        // // $curah_hujan=DB::table("tbl_curah_hujan")
        // //     ->select("id_curah_hujan", "id_region", "tahun", "bulan", "input_ke", "curah_hujan", "curah_hujan_normal")
        // //     ->where("tahun", "2023")
        // //     ->orderBy("id_region")
        // //     ->get();
        // // $curah_hujan=json_decode(json_encode($curah_hujan), true);

        // return response()->json([
        //     'data'=>$curah_hujan
        // ]);

        // $ch=DB::table("tbl_curah_hujan")->orderBy("id_region")->orderBy("tahun")->orderBy("bulan")->orderBy("input_ke")->get();
        // $ch=json_decode(json_encode($ch), true);
        
        // $arr="";
        // $count=0;
        // foreach($ch as $val){
        //     $tahun=$val['tahun'];
        //     $bulan=$val['bulan'];
        //     $input_ke=$val['input_ke'];
        //     $region=$val['id_region'];

        //     $str=$tahun."_".$bulan."_".$input_ke."_".$region;

        //     if($arr==$str){
        //         //CurahHujanModel::where("id_curah_hujan", $val['id_curah_hujan'])->delete();
        //         $count++;
        //     }
        //     else{
        //         $arr=$str;
        //     }
        // }

        // return response()->json(['s'=>"ok", 'count'=>$count]);

        // $s=CurahHujanNormalModel::where("id_region", 3)->first();
        // if(isset($s)){
        //     return response()->json(['ada'=>"y"]);
        // }
        // else{
        //     return response()->json(['ada'=>"n"]);
        // }

        // $a=null;

        // echo !is_null($a)?$a:"kosong";

        // $curah_hujan=DB::select(DB::raw("select id_curah_hujan, id_region, tahun, bulan, input_ke, max(curah_hujan) as curah_hujan, curah_hujan_normal from (SELECT a.id_curah_hujan, a.curah_hujan, a.id_region, a.tahun, a.bulan, a.input_ke, b.curah_hujan_normal FROM tbl_curah_hujan a left join tbl_curah_hujan_normal b ON a.id_region=b.id_region and a.bulan=b.bulan and a.input_ke=b.input_ke WHERE tahun=2024 UNION SELECT a.id_curah_hujan_normal, '' as curah_hujan, a.id_region, '2024' as tahun, a.bulan, a.input_ke, a.curah_hujan_normal FROM tbl_curah_hujan_normal a) t2 group by id_region, bulan, input_ke"))->get();

        // return response()->json(['data'=>$curah_hujan]);
        
        $tahun=2023;
        $curah_hujan=DB::table("tbl_curah_hujan_normal as a")
            ->leftJoin("tbl_curah_hujan as b", function($join)use($tahun){
                $join->on("a.id_region", "=", "b.id_region");
                $join->on("a.bulan", "=", "b.bulan");
                $join->on("a.input_ke", "=", "b.input_ke");
                $join->on("b.tahun", DB::raw($tahun));
            })
            ->select("b.id_curah_hujan", "a.id_region", DB::raw($tahun." as tahun"), "a.bulan", "a.input_ke", DB::raw("coalesce(b.curah_hujan, '') as curah_hujan"), "a.curah_hujan_normal")
            ->whereIn("a.id_region", [4915, 4916])
            ->orderBy("a.id_region")
            ->get();
        $curah_hujan=json_decode(json_encode($curah_hujan), true);
        
        return response()->json(['data'=>$curah_hujan]);
    }
}
