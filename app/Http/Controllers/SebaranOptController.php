<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Repository\SebaranOptRepo;
use App\Models\SebaranOptModel;

class SebaranOptController extends Controller
{

    // public function add(Request $request)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //ROLE AUTHENTICATION
    //     if(false){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $validation=Validator::make($req, [
    //         'opt'   =>"required"
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()->first()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     DB::transaction(function() use($req){
    //         OptModel::create([
    //             'opt'   =>$req['opt']
    //         ]);
    //     });

    //     return response()->json([
    //         'status'=>"ok"
    //     ]);
    // }

    // public function update(Request $request, $id)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();
        
    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $req['id_opt']=$id;
    //     $validation=Validator::make($req, [
    //         'id_opt'=>"required|exists:App\Models\OptModel,id_opt",
    //         'opt'   =>"required"
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()->first()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     DB::transaction(function()use($req){
    //         OptModel::where("id_opt", $req['id_opt'])->update(['opt'=>$req['opt']]);
    //     });

    //     return response()->json([
    //         'status'=>"ok"
    //     ]);
    // }
    
    // public function delete(Request $request, $id)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();
        
    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $req['id_opt']=$id;
    //     $validation=Validator::make($req, [
    //         'id_opt'=>"required|exists:App\Models\OptModel,id_opt"
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()->first()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     DB::transaction(function() use($req){
    //         OptModel::where("id_opt", $req['id_opt'])->delete();
    //     });

    //     return response()->json([
    //         'status'=>"ok"
    //     ]);
    // }
    // public function add_multiple(Request $request)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();

    //     //ROLE AUTHENTICATION
    //     if(!in_array($login_data['role'], ['admin', 'kementan'])){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     //--messages
    //     $custom_error=[];
        
    //     foreach($req['data'] as $key=>$val){
    //         $custom_error['data.'.$key.'.regency_id.required']="Baris ".($key+1).", Kabupaten/Kota harus diisi!";
    //         $custom_error['data.'.$key.'.regency_id.exists']="Baris ".($key+1).", Kabupaten/Kota tidak valid!";
    //         $custom_error['data.'.$key.'.tahun.required']="Baris ".($key+1).", Tahun harus diisi!";
    //         $custom_error['data.'.$key.'.tahun.date_format']="Baris ".($key+1).", Format Tahun tidak valid!";
    //         $custom_error['data.'.$key.'.bulan.required']="Baris ".($key+1).", Bulan harus diisi!";
    //         $custom_error['data.'.$key.'.bulan.integer']=$custom_error['data.'.$key.'.bulan.min']=$custom_error['data.'.$key.'.bulan.max']="Baris ".($key+1).", Format Bulan tidak valid!";
    //         $custom_error['data.'.$key.'.komoditas.required']="Baris ".($key+1).", Komoditas harus diisi!";
    //         $custom_error['data.'.$key.'.komoditas.in']="Baris ".($key+1).", Komoditas Harus Berisi Aneka Cabai/Bawang Merah!";
    //         $custom_error['data.'.$key.'.opt.required']="Baris ".($key+1).", OPT harus diisi!";
    //         $custom_error['data.'.$key.'.lts_ringan.numeric']="Baris ".($key+1).", LTS Ringan tidak valid!";
    //         $custom_error['data.'.$key.'.lts_sedang.numeric']="Baris ".($key+1).", LTS Sedang tidak valid!";
    //         $custom_error['data.'.$key.'.lts_berat.numeric']="Baris ".($key+1).", LTS Berat tidak valid!";
    //         $custom_error['data.'.$key.'.sum_lts.numeric']="Baris ".($key+1).", Sum LTS tidak valid!";
    //         $custom_error['data.'.$key.'.lts_puso.numeric']="Baris ".($key+1).", LTS Puso tidak valid!";
    //     }

    //     //--start validation
    //     $validation=Validator::make($req, [
    //         'data'      =>[
    //             Rule::requiredIf(!isset($req['data'])),
    //             "array",
    //             "min:0"
    //         ],
    //         'data.*.regency_id' =>[
    //             "required",
    //             Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
    //                 return $q->where("type", "kabupaten_kota");
    //             })
    //         ],
    //         'data.*.tahun'      =>"required|date_format:Y",
    //         'data.*.bulan'      =>"required|integer|min:1|max:12",
    //         'data.*.komoditas'  =>"required|in:Aneka Cabai,Bawang Merah",
    //         'data.*.opt'        =>"required",
    //         'data.*.lts_ringan' =>"present|numeric",
    //         'data.*.lts_sedang' =>"present|numeric",
    //         'data.*.lts_berat'  =>"present|numeric",
    //         'data.*.sum_lts'    =>"present|numeric",
    //         'data.*.lts_puso'   =>"present|numeric"
    //     ], $custom_error);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()->first()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     DB::transaction(function() use($req){
    //         foreach($req['data'] as $val){
    //             SebaranOptModel::updateOrCreate(
    //                 [
    //                     'bulan'     =>$val['bulan'],
    //                     'tahun'     =>$val['tahun'],
    //                     'id_region' =>$val['regency_id'],
    //                     'komoditas' =>$val['komoditas'],
    //                     'opt'       =>$val['opt']
    //                 ],
    //                 [
    //                     'provinsi'  =>"",
    //                     'kab_kota'  =>"",
    //                     'lts_ringan'=>$val['lts_ringan']!=""?$val['lts_ringan']:null,
    //                     'lts_sedang'=>$val['lts_sedang']!=""?$val['lts_sedang']:null,
    //                     'lts_berat' =>$val['lts_berat']!=""?$val['lts_berat']:null,
    //                     'sum_lts'   =>$val['sum_lts']!=""?$val['sum_lts']:null,
    //                     'lts_puso'  =>$val['lts_puso']!=""?$val['lts_puso']:null
    //                 ]
    //             );
    //         }
    //     });

    //     return response()->json([
    //         'status'=>"ok"
    //     ]);
    // }
    public function import_chunks(Request $request)
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
        // // --start validation
        // $validation=Validator::make($req, [
        //     'data'      =>[
        //         Rule::requiredIf(!isset($req['data'])),
        //         "array",
        //         "min:0"
        //     ],
        //     'data.*.regency_id' =>[
        //         "required",
        //         Rule::exists("App\Models\RegionModel", "id_region")->where(function($q){
        //             return $q->where("type", "kabupaten_kota");
        //         })
        //     ],
        //     'data.*.tahun'      =>"present|date_format:Y",
        //     'data.*.bulan'      =>"present|integer|min:1|max:12",
        //     'data.*.periode'    =>"present",
        //     'data.*.kategori'   =>"present",
        //     'data.*.komoditas'  =>"present|in:Aneka Cabai,Bawang Merah",
        //     'data.*.jenis_varietas' =>"present",
        //     'data.*.satuan'     =>"present",
        //     'data.*.opt'        =>"present",
        //     'data.*.lts_ringan' =>"present|numeric",
        //     'data.*.lts_sedang' =>"present|numeric",
        //     'data.*.lts_berat'  =>"present|numeric",
        //     'data.*.lts_puso'   =>"present|numeric",
        //     'data.*.lks_ringan' =>"present|numeric",
        //     'data.*.lks_sedang' =>"present|numeric",
        //     'data.*.lks_berat'  =>"present|numeric",
        //     'data.*.lks_puso'   =>"present|numeric",
        //     'data.*.lp_pemusnahan'      =>"present|numeric",
        //     'data.*.lp_pestisida_kimia' =>"present|numeric",
        //     'data.*.lp_cara_lain'       =>"present|numeric",
        //     'data.*.lp_agens_hayati'    =>"present|numeric",
        //     'data.*.sum_lts'    =>"present|numeric",
        //     'data.*.sum_lks'    =>"present|numeric",
        //     'data.*.sum_lp'     =>"present|numeric"
        // ]);
        // if($validation->fails()){
        //     return response()->json([
        //         'error' =>"VALIDATION_ERROR",
        //         'data'  =>$validation->errors()->first()
        //     ], 500);
        // }

        //SUCCESS
        DB::transaction(function() use($req){
            $updated_at=date("Y-m-d H:i:s");
            $insert_data=[];
            foreach($req['data'] as $val){
                $insert_data[]=[
                    'id_region'     =>$val['regency_id'],
                    'tahun'         =>$val['tahun']!=""?$val['tahun']:"",
                    'bulan'         =>$val['bulan']!=""?$val['bulan']:"",
                    'periode'       =>$val['periode']!=""?$val['periode']:"",
                    'kategori'      =>$val['kategori']!=""?$val['kategori']:"",
                    'komoditas'     =>$val['komoditas']!=""?$val['komoditas']:"",
                    'jenis_varietas'=>$val['jenis_varietas']!=""?$val['jenis_varietas']:"",
                    'satuan'        =>$val['satuan']!=""?$val['satuan']:"",
                    'opt'           =>$val['opt']!=""?$val['opt']:"",
                    'lts_ringan'    =>trim($val['lts_ringan'])!=""?$val['lts_ringan']:null,
                    'lts_sedang'    =>trim($val['lts_sedang'])!=""?$val['lts_sedang']:null,
                    'lts_berat'     =>trim($val['lts_berat'])!=""?$val['lts_berat']:null,
                    'lts_puso'      =>trim($val['lts_puso'])!=""?$val['lts_puso']:null,
                    'lks_ringan'    =>trim($val['lks_ringan'])!=""?$val['lks_ringan']:null,
                    'lks_sedang'    =>trim($val['lks_sedang'])!=""?$val['lks_sedang']:null,
                    'lks_berat'     =>trim($val['lks_berat'])!=""?$val['lks_berat']:null,
                    'lks_puso'      =>trim($val['lks_puso'])!=""?$val['lks_puso']:null,
                    'lp_pemusnahan'      =>trim($val['lp_pemusnahan'])!=""?$val['lp_pemusnahan']:null,
                    'lp_pestisida_kimia' =>trim($val['lp_pestisida_kimia'])!=""?$val['lp_pestisida_kimia']:null,
                    'lp_cara_lain'       =>trim($val['lp_cara_lain'])!=""?$val['lp_cara_lain']:null,
                    'lp_agens_hayati'    =>trim($val['lp_agens_hayati'])!=""?$val['lp_agens_hayati']:null,
                    'sum_lts'       =>trim($val['sum_lts'])!=""?$val['sum_lts']:null,
                    'sum_lks'       =>trim($val['sum_lks'])!=""?$val['sum_lks']:null,
                    'sum_lp'        =>trim($val['sum_lp'])!=""?$val['sum_lp']:null,
                    'updated_at'    =>$updated_at,
                    'created_at'    =>$updated_at
                ];
            }

            $insert_data=collect($insert_data);
            $insert_chunks=$insert_data->chunk(1500);

            foreach ($insert_chunks as $key=>$chunk){
                \DB::table('tbl_sebaran_opt')->insert($chunk->toArray());
            }
        });

        return response()->json([
            'status'=>"ok"
        ]);
    }

    public function gets(Request $request)
    {
        $login_data=$request['fm__login_data'];
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
        $opt=SebaranOptRepo::gets_sebaran_opt($req);

        return response()->json([
            'first_page'    =>1,
            'current_page'  =>$opt['current_page'],
            'last_page'     =>$opt['last_page'],
            'data'          =>$opt['data']
        ]);
    }

    public function gets_region_kabupaten_kota(Request $request)
    {
        $login_data=$request['fm__login_data'];
        $req=$request->all();
        
        //VALIDATION
        $validation=Validator::make($req, [
            'pulau'      =>"nullable",
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
        $opt=SebaranOptRepo::gets_region_kabupaten_kota($req);

        return response()->json([
            'data'          =>$opt
        ]);
    }

    // public function get(Request $request, $id)
    // {
    //     $login_data=$request['fm__login_data'];
    //     $req=$request->all();
        
    //     //ROLE AUTHENTICATION
    //     if(false){
    //         return response()->json([
    //             'error' =>"ACCESS_NOT_ALLOWED"
    //         ], 403);
    //     }

    //     //VALIDATION
    //     $req['id_opt']=$id;
    //     $validation=Validator::make($req, [
    //         'id_opt'=>"required|exists:App\Models\OptModel,id_opt"
    //     ]);
    //     if($validation->fails()){
    //         return response()->json([
    //             'error' =>"VALIDATION_ERROR",
    //             'data'  =>$validation->errors()->first()
    //         ], 500);
    //     }

    //     //SUCCESS
    //     $opt=OptRepo::get_opt($req['id_opt']);
        
    //     return response()->json([
    //         'data'  =>$opt
    //     ]);
    // }
}
