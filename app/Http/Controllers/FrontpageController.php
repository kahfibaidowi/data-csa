<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\FrontpageModel;
use App\Repository\FrontpageRepo;

class FrontpageController extends Controller
{

    public function get_summary_ews_produksi(Request $request)
    {
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'tahun' =>"required|date_format:Y"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $summary=FrontpageRepo::get_summary_ews_produksi($req);

        return response()->json([
            'data'  =>$summary
        ]);
    }

    public function get_summary_sifat_hujan_kabupaten_kota(Request $request)
    {
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'tahun' =>"required|date_format:Y",
            'q'     =>"nullable",
            'per_page'  =>"nullable|integer|min:1"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $summary=FrontpageRepo::get_summary_sifat_hujan_kabupaten_kota($req);

        return response()->json([
            'data'      =>$summary
        ]);
    }

    public function get_summary_sifat_hujan_kecamatan(Request $request)
    {
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'tahun' =>"required|date_format:Y"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $summary=FrontpageRepo::get_summary_sifat_hujan_kecamatan($req);

        return response()->json([
            'data'  =>$summary
        ]);
    }

    public function get_jadwal_tanam_kecamatan(Request $request)
    {
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'tahun' =>"required|date_format:Y",
            'q'     =>"nullable",
            'per_page'  =>"nullable|integer|min:1"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $summary=FrontpageRepo::get_jadwal_tanam_kecamatan($req);

        return response()->json([
            'first_page'=>1,
            'data'      =>$summary['data'],
            'current_page'  =>$summary['current_page'],
            'last_page'     =>$summary['last_page']
        ]);
    }

    public function gets_region_provinsi(Request $request)
    {
        $req=$request->all();

        //SUCCESS
        $provinsi=FrontpageRepo::gets_region_provinsi($req);

        return response()->json([
            'data'  =>$provinsi
        ]);
    }

    public function gets_region_kabupaten_kota(Request $request)
    {
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'province_id'   =>[
                "nullable",
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
                    return $q->where("type", "provinsi");
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
        $kabkota=FrontpageRepo::gets_region_kabupaten_kota($req);

        return response()->json([
            'data'  =>$kabkota
        ]);
    }

    public function gets_sebaran_opt(Request $request)
    {
        $req=$request->all();
        
        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>"nullable|integer|min:1",
            'komoditas' =>"nullable",
            'tahun'     =>"nullable|date_format:Y",
            'bulan'     =>"nullable|between:1,12",
            'province_id'=>[
                "nullable",
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
                    return $q->where("type", "provinsi");
                })
            ],
            'regency_id'=>[
                "nullable",
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
        $sebaran_opt=FrontpageRepo::gets_sebaran_opt($req);

        return response()->json([
            'data'  =>[
                'first_page'    =>1,
                'current_page'  =>$sebaran_opt['data']['current_page'],
                'last_page'     =>$sebaran_opt['data']['last_page'],
                'data'          =>$sebaran_opt['data']['data']
            ],
            'infografis'        =>$sebaran_opt['infografis']
        ]);
    }

    public function gets_sebaran_opt_region(Request $request)
    {
        //SUCCESS
        $sebaran_region=FrontpageRepo::gets_sebaran_region();

        return response()->json([
            'data'  =>$sebaran_region
        ]);
    }

    public function gets_region_sebaran_opt(Request $request)
    {
        $req=$request->all();
        
        //VALIDATION
        $validation=Validator::make($req, [
            'per_page'  =>"nullable|integer|min:1",
            'komoditas' =>"nullable",
            'tahun'     =>"nullable|date_format:Y",
            'bulan'     =>"nullable|between:1,12",
            'province_id'=>[
                "nullable",
                Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
                    return $q->where("type", "provinsi");
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
        $region=FrontpageRepo::gets_region_sebaran_opt($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$region['current_page'],
            'last_page'     =>$region['last_page'],
            'data'          =>$region['data']
        ]);
    }

    // public function gets_curah_hujan_kecamatan(Request $request)
    // {
    //     $req=$request->all();

    //     //VALIDATION
    //     $validation=Validator::make($req, [
    //         'tahun'         =>"required|date_format:Y",
    //         'regency_id'    =>[
    //             "nullable",
    //             Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
    //                 return $q->where("type", "kabupaten_kota");
    //             })
    //         ]
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()->first()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     $curah_hujan=FrontpageRepo::gets_curah_hujan_kecamatan($req);

    //     return response()->json([
    //         'data'  =>$curah_hujan
    //     ]);
    // }

    public function gets_geojson_curah_hujan_kecamatan(Request $request)
    {
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'regency_id'    =>[
                "nullable",
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
        $geojson=FrontpageRepo::gets_geojson_curah_hujan_kecamatan($req);

        return response()->json($geojson);
    }

    public function gets_region_kecamatan(Request $request)
    {
        $req=$request->all();

        //VALIDATION
        $validation=Validator::make($req, [
            'regency_id'    =>[
                "nullable",
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
        $curah_hujan=FrontpageRepo::gets_region_kecamatan($req);

        return response()->json([
            'data'  =>$curah_hujan
        ]);
    }

    //ADMIN
    public function upsert_widget(Request $request)
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
            'type'  =>"required",
            'data'  =>"nullable|array"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            FrontpageModel::updateOrCreate(
                [
                    'type'  =>$req['type']
                ],
                [
                    'data'  =>isset($req['data'])?$req['data']:[]
                ]
            );
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function get_widget(Request $request)
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
            'type'  =>"required|exists:App\Models\FrontpageModel,type",
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $widget=FrontpageRepo::get_widget($req['type']);

        return response()->json([
            'data'  =>$widget
        ]);
    }

    public function add_post(Request $request)
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
            'title'     =>"required",
            'kategori'  =>"nullable|array",
            'featured_image'=>"required",
            'content'   =>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req, $login_data){
            FrontpageModel::create([
                'id_user'   =>$login_data['id_user'],
                'type'      =>"post",
                'data'      =>[
                    'title'     =>$req['title'],
                    'kategori'  =>isset($req['kategori'])?$req['kategori']:[],
                    'featured_image'=>$req['featured_image'],
                    'content'   =>$req['content']
                ]
            ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function update_post(Request $request, $id)
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
        $req['id_frontpage']=$id;
        $validation=Validator::make($req, [
            'id_frontpage'  =>[
                "required",
                Rule::exists("App\Models\FrontpageModel")->where(function($q){
                    return $q->where("type", "post");
                })
            ],
            'title'     =>"required",
            'kategori'  =>"nullable|array",
            'featured_image'=>"required",
            'content'   =>"required"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            FrontpageModel::where("id_frontpage", $req['id_frontpage'])
                ->update([
                    'data'      =>[
                        'title'     =>$req['title'],
                        'kategori'  =>isset($req['kategori'])?$req['kategori']:[],
                        'featured_image'=>$req['featured_image'],
                        'content'   =>$req['content']
                    ]
                ]);
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function delete_post(Request $request, $id)
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
        $req['id_frontpage']=$id;
        $validation=Validator::make($req, [
            'id_frontpage'  =>[
                "required",
                Rule::exists("App\Models\FrontpageModel")->where(function($q){
                    return $q->where("type", "post");
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
        DB::transaction(function() use($req){
            FrontpageModel::where("id_frontpage", $req['id_frontpage'])->delete();
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets_post(Request $request)
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
            'q'         =>"nullable",
            'per_page'  =>"nullable|integer|min:1"
        ]);
        if($validation->fails()){
            return response()->json([
                'error' =>"VALIDATION_ERROR",
                'data'  =>$validation->errors()->first()
            ], 500);
        }

        //SUCCESS
        $posts=FrontpageRepo::gets_post($req);

        return response()->json([
            'first_page'=>1,
            'data'      =>$posts['data'],
            'current_page'  =>$posts['current_page'],
            'last_page'     =>$posts['last_page']
        ]);
    }

    public function get_post(Request $request, $id)
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
        $req['id_frontpage']=$id;
        $validation=Validator::make($req, [
            'id_frontpage'  =>[
                "required",
                Rule::exists("App\Models\FrontpageModel")->where(function($q){
                    return $q->where("type", "post");
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
        $post=FrontpageRepo::get_post($req['id_frontpage']);

        return response()->json([
            'data'      =>$post
        ]);
    }

    public function gets_post_kategori(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //SUCCESS
        $q_kategori=FrontpageModel::select("data->kategori as kategori")->where("type", "post")->get();

        $kategori=[];
        foreach($q_kategori as $val){
            $kategori=array_merge($kategori, json_decode($val['kategori']));
        }
        $kategori=array_unique($kategori);

        return response()->json([
            'data'  =>$kategori
        ]);
    }

    public function update_geojson_kecamatan(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();

        //ROLE AUTHENTICATION
        if(!in_array($login_data['role'], ['admin', 'kementan'])){
            return response()->json([
                'error' =>"ACCESS_NOT_ALLOWED"
            ], 403);
        }

        //SUCCESS
        DB::transaction(function() use($req){
            $geojson=FrontpageRepo::gets_geojson_curah_hujan_kecamatan([]);

            $f_json=fopen(storage_path(env("UPLOAD_PATH"))."/fp_geojson_kecamatan.json", "w") or die("Unable to open file!");
            $contents=json_encode($geojson);
            fwrite($f_json, $contents);
            fclose($f_json);

            $gjk=FrontpageModel::where("type", "geojson_kecamatan")->lockForUpdate()->first();
            $date=Carbon::parse(date("Y-m-d H:i:s"))->timezone(env("APP_TIMEZONE"));
            if(!is_null($gjk)){
                FrontpageModel::where("type", "geojson_kecamatan")
                    ->update([
                        'data'=>array_merge($gjk['data'], [
                            'file_update'=>$date
                        ]
                    )
                ]);
            }
            else{
                FrontpageModel::create([
                    'type'=>"geojson_kecamatan",
                    'data'=>[
                        'file_update'   =>$date,
                        'db_update'     =>""
                    ]
                ]);
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }
}
