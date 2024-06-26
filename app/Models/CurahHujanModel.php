<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CurahHujanModel extends Model{

    use \Awobaz\Compoships\Compoships;

    protected $table="tbl_curah_hujan";
    protected $primaryKey="id_curah_hujan";
    protected $fillable=[
        "id_region",
        "tahun",
        "bulan",
        "input_ke",
        "curah_hujan",
        "curah_hujan_normal",
        "updated_at"
    ];
    protected $perPage=99999999999999999999;


    /*
     *#FUNCTION
     *
     */
    public function region(){
        return $this->belongsTo(RegionModel::class, "id_region", "id_region");
    }
    public function ch_normal(){
        return $this->belongsTo(CurahHujanNormalModel::class, ["id_region", "bulan", "input_ke"], ["id_region", "bulan", "input_ke"]);
    }
}
