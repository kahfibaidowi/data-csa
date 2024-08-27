<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repository\BantuanDPIRepo;
use App\Models\BantuanDPIModel;

class BantuanDPIController extends Controller
{

    public function add(Request $request)
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
            'id_region'     =>[
                "required",
                Rule::exists("App\Models\RegionModel")->where(function($q){
                    $q->where("type", "kecamatan");
                })
            ],
            'tahun'         =>"required|date_format:Y",
            'jenis_bantuan' =>"required",
            'kelompok_tani' =>"required",
            'pj_kelompok_tani'  =>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            BantuanDPIModel::create([
                'id_region'     =>$req['id_region'],
                'tahun'         =>$req['tahun'],
                'jenis_bantuan' =>$req['jenis_bantuan'],
                'kelompok_tani' =>$req['kelompok_tani'],
                'pj_kelompok_tani'  =>$req['pj_kelompok_tani']
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
        if(!in_array($login_data['role'], ['admin', 'kementan'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //VALIDATION
        $req['id_bantuan_dpi']=$id;
        $validation=Validator::make($req, [
            'id_bantuan_dpi'=>"required|exists:App\Models\BantuanDPIModel,id_bantuan_dpi",
            'tahun'         =>"required|date_format:Y",
            'jenis_bantuan' =>"required",
            'kelompok_tani' =>"required",
            'pj_kelompok_tani'  =>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function()use($req){
            $data_update=[
                'tahun'         =>$req['tahun'],
                'jenis_bantuan' =>$req['jenis_bantuan'],
                'kelompok_tani' =>$req['kelompok_tani'],
                'pj_kelompok_tani'  =>$req['pj_kelompok_tani']
            ];
            BantuanDPIModel::where("id_bantuan_dpi", $req['id_bantuan_dpi'])->update($data_update);
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
        $req['id_bantuan_dpi']=$id;
        $validation=Validator::make($req, [
            'id_bantuan_dpi'=>"required|exists:App\Models\BantuanDPIModel,id_bantuan_dpi"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            BantuanDPIModel::where("id_bantuan_dpi", $req['id_bantuan_dpi'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets(Request $request)
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
            'tahun'     =>"present",
            'province_id'   =>"present",
            'regency_id'    =>"present",
            'district_id'   =>"present"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $opt=BantuanDPIRepo::gets($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$opt['current_page'],
            'last_page'     =>$opt['last_page'],
            'data'          =>$opt['data']
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
        $req['id_bantuan_dpi']=$id;
        $validation=Validator::make($req, [
            'id_bantuan_dpi'=>"required|exists:App\Models\BantuanDPIModel,id_bantuan_dpi"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $opt=BantuanDPIRepo::get($req['id_bantuan_dpi']);
        
        return response()->json([
            'data'  =>$opt
        ]);
    }
}
