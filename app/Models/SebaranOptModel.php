<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SebaranOptModel extends Model{

    protected $table="tbl_sebaran_opt";
    protected $primaryKey="id_sebaran_opt";
    protected $fillable=[
        "id_region",
        "bulan",
        "tahun",
        "periode",
        "kategori",
        "komoditas",
        "jenis_varietas",
        "satuan",
        "opt",
        "lts_ringan",
        "lts_sedang",
        "lts_berat",
        "lts_puso",
        "lks_ringan",
        "lks_sedang",
        "lks_berat",
        "lks_puso",
        "lp_pemusnahan",
        "lp_pestisida_kimia",
        "lp_cara_lain",
        "lp_agens_hayati",
        "sum_lts",
        "sum_lks",
        "sum_lp",
        "updated_at",
        "created_at"
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
