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
}
