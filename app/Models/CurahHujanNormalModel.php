<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CurahHujanNormalModel extends Model{

    use \Awobaz\Compoships\Compoships;

    
    protected $table="tbl_curah_hujan_normal";
    protected $primaryKey="id_curah_hujan_normal";
    protected $fillable=[
        "id_region",
        "bulan",
        "input_ke",
        "curah_hujan_normal"
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
