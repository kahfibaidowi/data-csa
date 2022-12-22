<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CurahHujanModel extends Model{

    protected $table="tbl_curah_hujan";
    protected $primaryKey="id_curah_hujan";
    protected $fillable=[
        "id_region",
        "tahun",
        "bulan",
        "curah_hujan",
        "curah_hujan_normal",
        "sifat"
    ];
    protected $perPage=99999999999999999999;


    /*
     *#FUNCTION
     *
     */
    public function region(){
        return $this->belongsTo(RegionModel::class, "id_region", "id_region");
    }
}
