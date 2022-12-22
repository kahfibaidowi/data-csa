<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\RegionModel;
use App\Repository\RegionRepo;

class RegionController extends Controller
{

    public function add(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'nested'    =>[
                Rule::requiredIf(function()use($req){
                    if(!isset($req['type'])) return true;
                    if(!isset($req['nested'])) return true;
                    if($req['type']!="provinsi"&&trim($req['nested'])=="") return true;
                    return false;
                }),
                function($attr, $value, $fail)use($req){
                    if(!isset($req['type']) || !isset($req['nested'])) return $fail("Nested error.");
                    if($req['type']=="provinsi"&&trim($req['nested'])!="") return $fail("Nested must empty.");
                },
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q)use($req){
                    if($req['type']=="provinsi") $type="";
                    elseif($req['type']=="kabupaten_kota") $type="provinsi";
                    elseif($req['type']=="kecamatan") $type="kabupaten_kota";

                    return $q->where("type", $type);
                })
            ],
            'type'      =>"required|in:provinsi,kabupaten_kota,kecamatan",
            'region'    =>"required",
            'geo_json'  =>[
                Rule::requiredIf(!isset($req['geo_json']))
            ],
            'map_center'=>[
                Rule::requiredIf(!isset($req['map_center']))
            ],
            'map_center.latitude'   =>[
                Rule::requiredIf(!isset($req['map_center']['latitude']))
            ],
            'map_center.longitude'  =>[
                Rule::requiredIf(!isset($req['map_center']['longitude']))
            ],
            'map_center.zoom'       =>[
                Rule::requiredIf(!isset($req['map_center']['zoom'])),
                "numeric"
            ],
            'zom'       =>[
                Rule::requiredIf(function()use($req){
                    if(!isset($req['zom']) || !isset($req['type'])) return true;
                    if($req['type']=="kecamatan") return true;
                    return false;
                })
            ],
            'pulau'     =>[
                Rule::requiredIf(function()use($req){
                    if(!isset($req['pulau']) || !isset($req['type'])) return true;
                    if($req['type']=="provinsi") return true;
                    return false;
                })
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function()use($req){
            $geo_json=[
                'graph'     =>count($req['geo_json'])==0?null:$req['geo_json'],
                'map_center'=>$req['map_center']
            ];

            switch($req['type']){
                case "provinsi":
                    $data=[
                        'pulau' =>$req['pulau']
                    ];
                break;
                case "kabupaten_kota":
                    $data=null;
                break;
                case "kecamatan":
                    $data=[
                        'zom'   =>$req['zom']
                    ];
                break;
            }

            RegionModel::create([
                'nested'=>trim($req['nested'])!=""?$req['nested']:null,
                'type'  =>$req['type'],
                'region'=>$req['region'],
                'data'  =>$data,
                'geo_json'  =>$geo_json
            ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function update(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_region']=$id;
        $validation=Validator::make($req, [
            'id_region' =>"required|exists:App\Models\RegionModel,id_region",
            'region'    =>"required",
            'nested'    =>[
                Rule::requiredIf(function()use($req){
                    $v=RegionModel::where("id_region", $req['id_region'])->first();

                    if(is_null($v) || !isset($req['nested'])) return true;
                    if($v['type']!="provinsi"&&trim($req['nested'])=="") return true;
                    return false;
                }),
                function($attr, $value, $fail)use($req){
                    $v=RegionModel::where("id_region", $req['id_region'])->first();

                    if(is_null($v) || !isset($req['nested'])) return $fail("Nested error.");
                    if($v['type']=="provinsi"&&trim($req['nested'])!="") return $fail("Nested must empty.");
                },
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q)use($req){
                    $v=RegionModel::where("id_region", $req['id_region'])->first();

                    if($v['type']=="provinsi") $type="";
                    elseif($v['type']=="kabupaten_kota") $type="provinsi";
                    elseif($v['type']=="kecamatan") $type="kabupaten_kota";

                    return $q->where("type", $type);
                })
            ],
            'geo_json'  =>[
                Rule::requiredIf(!isset($req['geo_json']))
            ],
            'map_center'=>[
                Rule::requiredIf(!isset($req['map_center']))
            ],
            'map_center.latitude'   =>[
                Rule::requiredIf(!isset($req['map_center']['latitude']))
            ],
            'map_center.longitude'  =>[
                Rule::requiredIf(!isset($req['map_center']['longitude']))
            ],
            'map_center.zoom'       =>[
                Rule::requiredIf(!isset($req['map_center']['zoom'])),
                "numeric"
            ],
            'zom'       =>[
                Rule::requiredIf(function()use($req){
                    $v=RegionModel::where("id_region", $req['id_region'])->first();

                    if(!isset($req['zom'])) return true;
                    if($v['type']=="kecamatan") return true;
                    return false;
                })
            ],
            'pulau'     =>[
                Rule::requiredIf(function()use($req){
                    $v=RegionModel::where("id_region", $req['id_region'])->first();

                    if(!isset($req['pulau'])) return true;
                    if($v['type']=="provinsi") return true;
                    return false;
                })
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function()use($req){
            $region=RegionModel::find($req['id_region']);
            
            $geo_json=[
                'graph'     =>count($req['geo_json'])==0?null:$req['geo_json'],
                'map_center'=>$req['map_center']
            ];

            switch($region['type']){
                case "provinsi":
                    $data=[
                        'pulau' =>$req['pulau']
                    ];
                break;
                case "kabupaten_kota":
                    $data=null;
                break;
                case "kecamatan":
                    $data=[
                        'zom'   =>$req['zom']
                    ];
                break;
            }

            RegionModel::where("id_region", $req['id_region'])
                ->update([
                    'nested'=>trim($req['nested'])!=""?$req['nested']:null,
                    'region'=>$req['region'],
                    'data'  =>$data,
                    'geo_json'  =>$geo_json
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
    
    public function delete(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_region']=$id;
        $validation=Validator::make($req, [
            'id_region'=>"required|exists:App\Models\RegionModel,id_region"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function()use($req){
            RegionModel::where("id_region", $req['id_region'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function add_multiple(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'csv'   =>"required|array",
            'csv.*.kabupaten_kota'  =>"required",
            'csv.*.provinsi'        =>"required",
            'csv.*.kecamatan'       =>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        // DB::transaction(function()use($req){
        //     foreach($req['csv'] as $val){
        //         //provinsi
        //         $p=RegionModel::where("type", "provinsi")->where("region", $val['provinsi'])->first();
        //         if(is_null($p)){
        //             $p=RegionModel::create([
        //                 'nested'=>null,
        //                 'type'  =>"provinsi",
        //                 'region'=>$val['provinsi'],
        //                 'data'  =>[
        //                     'pulau' =>""
        //                 ],
        //                 'geo_json'  =>[
        //                     'graph'     =>null,
        //                     'map_center'=>[
        //                         'latitude'  =>"",
        //                         'longitude' =>"",
        //                         'zoom'      =>""
        //                     ]
        //                 ]
        //             ]);
        //         }

        //         //kabupaten kota
        //         $k=RegionModel::where("type", "kabupaten_kota")->where("region", $val['kabupaten_kota'])->where("nested", $p['id_region'])->first();
        //         if(is_null($k)){
        //             $k=RegionModel::create([
        //                 'nested'=>$p['id_region'],
        //                 'type'  =>"kabupaten_kota",
        //                 'region'=>$val['kabupaten_kota'],
        //                 'data'  =>null,
        //                 'geo_json'  =>[
        //                     'graph'     =>null,
        //                     'map_center'=>[
        //                         'latitude'  =>"",
        //                         'longitude' =>"",
        //                         'zoom'      =>""
        //                     ]
        //                 ]
        //             ]);
        //         }

        //         //kecamatan
        //         RegionModel::create([
        //             'nested'=>$k['id_region'],
        //             'type'  =>"kecamatan",
        //             'region'=>$val['kecamatan'],
        //             'data'  =>[
        //                 'zom'   =>$val['zom']
        //             ],
        //             'geo_json'  =>[
        //                 'graph'     =>null,
        //                 'map_center'=>[
        //                     'latitude'  =>"",
        //                     'longitude' =>"",
        //                     'zoom'      =>""
        //                 ]
        //             ]
        //         ]);
        //     }
        // });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    //GET
    public function gets_pulau(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(false){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>[
                Rule::requiredIf(!isset($req['per_page'])),
                'integer',
                'min:1'
            ],
            'q'         =>[
                Rule::requiredIf(!isset($req['q']))
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $region=RegionRepo::gets_pulau($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$region['current_page'],
            'last_page'     =>$region['last_page'],
            'data'          =>$region['data']
        ]);
    }

    public function gets_provinsi(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(false){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>[
                Rule::requiredIf(!isset($req['per_page'])),
                'integer',
                'min:1'
            ],
            'q'         =>[
                Rule::requiredIf(!isset($req['q']))
            ],
            'pulau'     =>[
                Rule::requiredIf(!isset($req['pulau']))
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $region=RegionRepo::gets_provinsi($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$region['current_page'],
            'last_page'     =>$region['last_page'],
            'data'          =>$region['data']
        ]);
    }
    
    public function gets_kabupaten_kota(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(false){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>[
                Rule::requiredIf(!isset($req['per_page'])),
                'integer',
                'min:1'
            ],
            'province_id'   =>[
                Rule::requiredIf(!isset($req['province_id'])),
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
                    return $q->where("type", "provinsi");
                })
            ],
            'q'         =>[
                Rule::requiredIf(!isset($req['q']))
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $region=RegionRepo::gets_kabupaten_kota($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$region['current_page'],
            'last_page'     =>$region['last_page'],
            'data'          =>$region['data']
        ]);
    }

    public function gets_kecamatan(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(false){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>[
                Rule::requiredIf(!isset($req['per_page'])),
                'integer',
                'min:1'
            ],
            'q'         =>[
                Rule::requiredIf(!isset($req['q']))
            ],
            'province_id'=>[
                Rule::requiredIf(!isset($req['province_id'])),
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
                    return $q->where("type", "provinsi");
                })
            ],
            'regency_id'=>[
                Rule::requiredIf(!isset($req['regency_id'])),
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
                    return $q->where("type", "kabupaten_kota");
                })
            ]
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $region=RegionRepo::gets_kecamatan($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$region['current_page'],
            'last_page'     =>$region['last_page'],
            'data'          =>$region['data']
        ]);
    }

    public function get(Request $request, $id)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(false){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_region']=$id;
        $validation=Validator::make($req, [
            'id_region'=>"required|exists:App\Models\RegionModel,id_region"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $region=RegionRepo::get_region($req['id_region']);

        return response()->json([
            'data'  =>$region
        ]);
    }
}