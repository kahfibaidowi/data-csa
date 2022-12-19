<?php

namespace App\Repository;

use App\Models\OptModel;


class OptRepo{
    
    public static function get_opt($opt_id)
    {
        //query
        $query=OptModel::where("id_opt", $opt_id);
        
        //return
        return optional($query->first())->toArray();
    }

    public static function gets_opt($params)
    {
        //params
        $params['per_page']=trim($params['per_page']);

        //query
        $query=OptModel::select("id_opt", "opt");
        //--q
        $query=$query->where("opt", "like", "%".$params['q']."%");
        //--order
        $query=$query->orderBy("opt");

        //return
        return $query->paginate($params['per_page'])->toArray();
    }
}