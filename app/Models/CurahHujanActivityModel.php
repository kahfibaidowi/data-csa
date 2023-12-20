<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CurahHujanActivityModel extends Model{

    use \Awobaz\Compoships\Compoships;

    protected $table="tbl_curah_hujan_activity";
    protected $primaryKey="id_curah_hujan_activity";
    protected $fillable=[
        "id_region",
        "tahun",
        "bulan",
        "input_ke",
        "curah_hujan",
        "id_user",
        "info_device",
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
    public function user(){
        return $this->belongsTo(UserModel::class, "id_user", "id_user");
    }
}
