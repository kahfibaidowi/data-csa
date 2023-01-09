<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
            'tahun' =>"required|date_format:Y"
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
            'data'  =>$summary
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
}
