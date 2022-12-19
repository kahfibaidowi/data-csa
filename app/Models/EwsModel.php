<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EwsModel extends Model{

    protected $table="tbl_ews";
    protected $primaryKey="id_ews";
    protected $fillable=[
        "id_region",
        "type",
        "tahun",
        "bulan",
        "curah_hujan",
        "opt_utama",
        "produksi"
    ];
    protected $casts=[
        "opt_utama" =>"array"
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
