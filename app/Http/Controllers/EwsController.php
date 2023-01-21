<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repository\EwsRepo;
use App\Models\EwsModel;

class EwsController extends Controller
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
            'type'      =>"required",
            'tahun'     =>"required|date_format:Y",
            'bulan'     =>"required|integer|min:1|max:12",
            'input_ke'  =>"required|integer|min:1|max:3",
            'opt_utama' =>[
                Rule::requiredIf(!isset($req['opt_utama'])),
                "array",
                "min:0"
            ],
            'produksi'  =>"required|numeric|min:0"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $ews=(object)[];
        DB::transaction(function() use($req, &$ews){
            $update=EwsModel::updateOrCreate(
                [
                    'id_region' =>$req['id_region'],
                    'type'      =>$req['type'],
                    'tahun'     =>$req['tahun'],
                    'bulan'     =>$req['bulan'],
                    'input_ke'  =>$req['input_ke']
                ],
                [
                    'opt_utama' =>$req['opt_utama'],
                    'produksi'  =>$req['produksi']
                ]
            );

            $ews=$update;
        });

        return response()->json([
            'status'=>"ok",
            'data'  =>$ews
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
        $req['id_ews']=$id;
        $validation=Validator::make($req, [
            'id_ews'=>"required|exists:App\Models\EwsModel,id_ews"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            EwsModel::where("id_ews", $req['id_ews'])->delete();
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
            'type'      =>"required",
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
        $ews=EwsRepo::gets_ews_kecamatan($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$ews['current_page'],
            'last_page'     =>$ews['last_page'],
            'data'          =>$ews['data']
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
            'type'      =>"required",
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
        $ews=EwsRepo::gets_ews_kabupaten_kota($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$ews['current_page'],
            'last_page'     =>$ews['last_page'],
            'data'          =>$ews['data']
        ]);
    }
}
