<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class RegionModel extends Model{

    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;
    use \Staudenmeir\EloquentHasManyDeep\HasTableAlias;

    protected $table="tbl_region";
    protected $primaryKey="id_region";
    protected $fillable=[
        "nested",
        "type",
        "region",
        "geo_json",
        "data"
    ];
    protected $casts=[
        "geo_json"  =>"array",
        "data"      =>"array"
    ];
    protected $perPage=99999999999999999999;


    /*
     *#FUNCTION
     *
     */
    public function kabupaten_kota(){
        return $this->hasMany(RegionModel::class, "nested", "id_region")->where("type", "kabupaten_kota")->orderBy("region");
    }
    public function kecamatan(){
        return $this->hasMany(RegionModel::class, "nested", "id_region")->where("type", "kecamatan")->orderBy("region");
    }
    public function parent(){
        return $this->belongsTo(RegionModel::class, "nested", "id_region");
    }

    //--ews
    public function ews(){
        return $this->hasMany(EwsModel::class, "id_region", "id_region");
    }
    public function ews_kabupaten_kota(){
        return $this->hasManyThrough(
            EwsModel::class,
            RegionModel::class,
            "nested",
            "id_region",
            "id_region",
            "id_region"
        );
    }
    public function ews_provinsi(){
        return $this->hasManyDeep(
            EwsModel::class,
            [RegionModel::class." as kab_kota", RegionModel::class." as kec"],
            [
                "nested",
                "nested",
                "id_region"
            ],
            [
                "id_region",
                "id_region",
                "id_region"
            ]
        );
    }

    //--curah hujan
    public function curah_hujan(){
        return $this->hasMany(CurahHujanModel::class, "id_region", "id_region");
    }
    public function curah_hujan_kabupaten_kota(){
        return $this->hasManyThrough(
            CurahHujanModel::class,
            RegionModel::class,
            "nested",
            "id_region",
            "id_region",
            "id_region"
        );
    }
    public function curah_hujan_provinsi(){
        return $this->hasManyDeep(
            CurahHujanModel::class,
            [RegionModel::class." as kab_kota", RegionModel::class." as kec"],
            [
                "nested",
                "nested",
                "id_region"
            ],
            [
                "id_region",
                "id_region",
                "id_region"
            ]
        );
    }
}
