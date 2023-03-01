<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repository\CurahHujanRepo;
use App\Models\CurahHujanModel;

class CurahHujanController extends Controller
{

    public function upsert(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'kementan'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'id_region' =>"required|exists:App\Models\RegionModel,id_region",
            'tahun'     =>"required|date_format:Y",
            'bulan'     =>"required|integer|min:1|max:12",
            'input_ke'  =>"required|integer|min:1|max:3",
            'curah_hujan'   =>"required|numeric",
            'curah_hujan_normal'=>"required|numeric"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $curah_hujan=(object)[];
        DB::transaction(function() use($req, &$curah_hujan){
            $update=CurahHujanModel::updateOrCreate(
                [
                    'id_region' =>$req['id_region'],
                    'tahun'     =>$req['tahun'],
                    'bulan'     =>$req['bulan'],
                    'input_ke'  =>$req['input_ke']
                ],
                [
                    'curah_hujan'       =>$req['curah_hujan'],
                    'curah_hujan_normal'=>$req['curah_hujan_normal'],
                ]
            );

            $curah_hujan=$update;
        });

        return response()->json([
            'status'=>"ok",
            'data'  =>$curah_hujan
        ]);
    }

    public function upsert_multiple(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'kementan'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'tahun'     =>"required|date_format:Y",
            'data'      =>[
                Rule::requiredIf(!isset($req['data'])),
                "array",
                "min:0"
            ],
            'data.*.id_region'  =>"required|exists:App\Models\RegionModel,id_region",
            'data.*.bulan'      =>"required|integer|min:1|max:12",
            'data.*.input_ke'   =>"required|integer|min:1|max:3",
            'data.*.curah_hujan'=>"required|numeric",
            'data.*.curah_hujan_normal' =>"required|numeric"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            foreach($req['data'] as $val){
                CurahHujanModel::updateOrCreate(
                    [
                        'id_region' =>$val['id_region'],
                        'tahun'     =>$req['tahun'],
                        'bulan'     =>$val['bulan'],
                        'input_ke'  =>$val['input_ke']
                    ],
                    [
                        'curah_hujan'       =>$val['curah_hujan'],
                        'curah_hujan_normal'=>$val['curah_hujan_normal'],
                    ]
                );
            }
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
        if(!in_array($login_data['role'], ['admin', 'kementan'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_curah_hujan']=$id;
        $validation=Validator::make($req, [
            'id_curah_hujan'=>"required|exists:App\Models\CurahHujanModel,id_curah_hujan"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            CurahHujanModel::where("id_curah_hujan", $req['id_curah_hujan'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_kecamatan(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();
        
        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'kementan'])){
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
            'tahun'     =>"required|date_format:Y",
            'regency_id'=>[
                Rule::requiredIf(!isset($req['regency_id'])),
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
                    return $q->where("type", "kabupaten_kota");
                })
            ],
            'province_id'=>[
                Rule::requiredIf(!isset($req['province_id'])),
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
                    return $q->where("type", "provinsi");
                })
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
        $curah_hujan=CurahHujanRepo::gets_curah_hujan_kecamatan($req);
        
        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$curah_hujan['current_page'],
            'last_page'     =>$curah_hujan['last_page'],
            'data'          =>$curah_hujan['data']
        ]);
    }

    public function gets_kabupaten_kota(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();
        
        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'kementan'])){
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
            'tahun'     =>"required|date_format:Y",
            'province_id'=>[
                Rule::requiredIf(!isset($req['province_id'])),
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
                    return $q->where("type", "provinsi");
                })
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
        $curah_hujan=CurahHujanRepo::gets_curah_hujan_kabupaten_kota($req);
        
        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$curah_hujan['current_page'],
            'last_page'     =>$curah_hujan['last_page'],
            'data'          =>$curah_hujan['data']
        ]);
    }

    public function gets_provinsi(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();
        
        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'kementan'])){
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
            'tahun'     =>"required|date_format:Y",
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
        $curah_hujan=CurahHujanRepo::gets_curah_hujan_provinsi($req);
        
        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$curah_hujan['current_page'],
            'last_page'     =>$curah_hujan['last_page'],
            'data'          =>$curah_hujan['data']
        ]);
    }

    public function gets_treeview(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();
        
        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'kementan'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $validation=Validator::make($req, [
            'tahun'     =>"required|date_format:Y"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $curah_hujan=CurahHujanRepo::gets_curah_hujan_treeview($req);
        
        return response()->json([
            'first_page'    =>1,
            'current_page'  =>1,
            'last_page'     =>1,
            'data'          =>$curah_hujan
        ]);
    }
}
