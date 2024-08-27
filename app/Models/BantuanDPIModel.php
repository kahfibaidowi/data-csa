<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BantuanDPIModel extends Model{


    protected $table="tbl_bantuan_dpi";
    protected $primaryKey="id_bantuan_dpi";
    protected $fillable=[
        "id_region",
        "tahun",
        "jenis_bantuan",
        "kelompok_tani",
        "pj_kelompok_tani"
    ];
    protected $casts=[
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
