<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class OptModel extends Model{

    protected $table="tbl_opt";
    protected $primaryKey="id_opt";
    protected $fillable=[
        "opt"
    ];
    protected $perPage=99999999999999999999;


    /*
     *#FUNCTION
     *
     */
}
