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
            $sifat=CurahHujanRepo::generate_sifat_hujan($req['curah_hujan'], $req['curah_hujan_normal']);
            $update=CurahHujanModel::updateOrCreate(
                [
                    'id_region' =>$req['id_region'],
                    'tahun'     =>$req['tahun'],
                    'bulan'     =>$req['bulan']
                ],
                [
                    'curah_hujan'       =>$req['curah_hujan'],
                    'curah_hujan_normal'=>$req['curah_hujan_normal'],
                    'sifat'             =>$sifat
                ]
            );

            $curah_hujan=$update;
        });

        return response()->json([
            'status'=>"ok",
            'data'  =>$curah_hujan
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
}
