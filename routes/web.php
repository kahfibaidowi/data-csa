<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//AUTHENTICATION
$router->group(['prefix'=>'/auth', 'middleware'=>'auth'], function()use($router){
    $router->get("/verify", ['uses'=>"AuthController@verify_login"]);
    $router->get("/profile", ['uses'=>"AuthController@get_profile"]);
    $router->put("/profile", ['uses'=>"AuthController@update_profile"]);
    $router->delete("/logout", ['uses'=>"AuthController@logout"]);
});
$router->post("/auth/login", ['uses'=>"AuthController@login"]);

//FILE
$router->group(['prefix'=>'/file', 'middleware'=>'auth'], function()use($router){
    $router->post("/upload", ['uses'=>"FileController@upload"]);
    $router->post("/upload_avatar", ['uses'=>"FileController@upload_avatar"]);
});
$router->get("/file/show/{file}", ['uses'=>"FileController@show"]);

//USER
$router->group(['prefix'=>'/user', 'middleware'=>'auth'], function()use($router){
    $router->get("/", ['uses'=>"UserController@gets"]);
    $router->get("/{id}", ['uses'=>"UserController@get"]);
    $router->post("/", ['uses'=>"UserController@add"]);
    $router->delete("/{id}", ['uses'=>"UserController@delete"]);
    $router->put("/{id}", ['uses'=>"UserController@update"]);
});

//USER LOGIN
$router->group(['prefix'=>'/user_login', 'middleware'=>'auth'], function()use($router){
    $router->get("/", ['uses'=>"UserLoginController@gets"]);
    $router->delete("/{id}", ['uses'=>"UserLoginController@delete"]);
    $router->delete("/type/expired", ['uses'=>"UserLoginController@delete_expired"]);
});

//REGION
$router->group(['prefix'=>'/region', 'middleware'=>'auth'], function()use($router){
    $router->get("/type/pulau", ['uses'=>"RegionController@gets_pulau"]);
    $router->get("/type/provinsi", ['uses'=>"RegionController@gets_provinsi"]);
    $router->get("/type/kabupaten_kota", ['uses'=>"RegionController@gets_kabupaten_kota"]);
    $router->get("/type/kecamatan", ['uses'=>"RegionController@gets_kecamatan"]);
    $router->get("/{id}", ['uses'=>"RegionController@get"]);
    $router->post("/", ['uses'=>"RegionController@add"]);
    $router->post("/type/multiple", ['uses'=>"RegionController@add_multiple"]);
    $router->delete("/{id}", ['uses'=>"RegionController@delete"]);
    $router->put("/{id}", ['uses'=>"RegionController@update"]);
});

//OPT
$router->group(['prefix'=>'/opt', 'middleware'=>'auth'], function()use($router){
    $router->get("/", ['uses'=>"OptController@gets"]);
    $router->get("/{id}", ['uses'=>"OptController@get"]);
    $router->post("/", ['uses'=>"OptController@add"]);
    $router->delete("/{id}", ['uses'=>"OptController@delete"]);
    $router->put("/{id}", ['uses'=>"OptController@update"]);
});

//SEBARAN OPT
$router->group(['prefix'=>'/sebaran_opt', 'middleware'=>'auth'], function()use($router){
    $router->get("/", ['uses'=>"SebaranOptController@gets"]);
    $router->get("/type/region_kabupaten_kota", ['uses'=>"SebaranOptController@gets_region_kabupaten_kota"]);
    $router->post("/type/import_chunks", ['uses'=>"SebaranOptController@import_chunks"]);
    // $router->get("/{id}", ['uses'=>"OptController@get"]);
    // $router->post("/type/multiple", ['uses'=>"SebaranOptController@add_multiple"]);
    // $router->delete("/{id}", ['uses'=>"OptController@delete"]);
    // $router->put("/{id}", ['uses'=>"OptController@update"]);
});

//BANTUAN DPI
$router->group(['prefix'=>'/bantuan_dpi', 'middleware'=>'auth'], function()use($router){
    $router->get("/", ['uses'=>"BantuanDPIController@gets"]);
    $router->get("/{id}", ['uses'=>"BantuanDPIController@get"]);
    $router->post("/", ['uses'=>"BantuanDPIController@add"]);
    $router->delete("/{id}", ['uses'=>"BantuanDPIController@delete"]);
    $router->put("/{id}", ['uses'=>"BantuanDPIController@update"]);
});

//CURAH HUJAN NORMAL
$router->group(['prefix'=>'/curah_hujan_normal', 'middleware'=>'auth'], function()use($router){
    $router->post("/action/copy_from_curah_hujan", ['uses'=>"CurahHujanNormalController@copy_from_curah_hujan"]);
    $router->post("/", ['uses'=>"CurahHujanNormalController@upsert"]);
    $router->post("/type/multiple", ['uses'=>"CurahHujanNormalController@upsert_multiple"]);
    // $router->delete("/{id}", ['uses'=>"CurahHujanController@delete"]);
    // $router->get("/type/kabupaten_kota", ['uses'=>"CurahHujanController@gets_kabupaten_kota"]);
    // $router->get("/type/kecamatan", ['uses'=>"CurahHujanController@gets_kecamatan"]);
    // $router->get("/type/provinsi", ['uses'=>"CurahHujanController@gets_provinsi"]);
    $router->get("/type/treeview", ['uses'=>"CurahHujanNormalController@gets_treeview"]);
});

//CURAH HUJAN
$router->group(['prefix'=>'/curah_hujan', 'middleware'=>'auth'], function()use($router){
    $router->post("/", ['uses'=>"CurahHujanController@upsert"]);
    $router->post("/type/multiple", ['uses'=>"CurahHujanController@upsert_multiple"]);
    $router->delete("/{id}", ['uses'=>"CurahHujanController@delete"]);
    $router->get("/type/kabupaten_kota", ['uses'=>"CurahHujanController@gets_kabupaten_kota"]);
    $router->get("/type/kecamatan", ['uses'=>"CurahHujanController@gets_kecamatan"]);
    $router->get("/type/provinsi", ['uses'=>"CurahHujanController@gets_provinsi"]);
    $router->get("/type/treeview", ['uses'=>"CurahHujanController@gets_treeview"]);
    $router->get("/type/kecamatan/activity", ['uses'=>"CurahHujanController@gets_activity"]);
    $router->post("/type/multiple/chunk", ['uses'=>"CurahHujanController@insert_chunk_multiple"]);
});

//EWS
$router->group(['prefix'=>'/ews', 'middleware'=>'auth'], function()use($router){
    $router->post("/", ['uses'=>"EwsController@upsert"]);
    $router->delete("/{id}", ['uses'=>"EwsController@delete"]);
    $router->get("/type/kabupaten_kota", ['uses'=>"EwsController@gets_kabupaten_kota"]);
    $router->get("/type/kecamatan", ['uses'=>"EwsController@gets_kecamatan"]);
    $router->get("/type/provinsi", ['uses'=>"EwsController@gets_provinsi"]);
    $router->get("/type/treeview", ['uses'=>"EwsController@gets_treeview"]);
});

//frontpage admin
$router->group(['prefix'=>"/frontpage_admin", 'middleware'=>'auth'], function()use($router){
    $router->post("/widget", ['uses'=>"FrontpageController@upsert_widget"]);
    $router->get("/widget", ['uses'=>"FrontpageController@get_widget"]);
    $router->post("/post", ['uses'=>"FrontpageController@add_post"]);
    $router->get("/post", ['uses'=>"FrontpageController@gets_post"]);
    $router->get("/post/{id}", ['uses'=>"FrontpageController@get_post"]);
    $router->put("/post/{id}", ['uses'=>"FrontpageController@update_post"]);
    $router->delete("/post/{id}", ['uses'=>"FrontpageController@delete_post"]);
    $router->get("/post_kategori", ['uses'=>"FrontpageController@gets_post_kategori"]);
    $router->put("/geojson_kecamatan", ['uses'=>"FrontpageController@update_geojson_kecamatan"]);
});

//frontpage
$router->group(['prefix'=>"/frontpage"], function()use($router){
    $router->get("/summary/type/ews_produksi", ['uses'=>"FrontpageController@get_summary_ews_produksi"]);
    $router->get("/summary/type/sifat_hujan_kabupaten_kota", ['uses'=>"FrontpageController@get_summary_sifat_hujan_kabupaten_kota"]);
    $router->get("/summary/type/sifat_hujan_kecamatan", ['uses'=>"FrontpageController@get_summary_sifat_hujan_kecamatan"]);
    $router->get("/summary/type/jadwal_tanam_kecamatan", ['uses'=>"FrontpageController@get_jadwal_tanam_kecamatan"]);
    $router->get("/summary/type/curah_hujan_kecamatan", ['uses'=>"FrontpageController@gets_curah_hujan_kecamatan"]);
    $router->get("/summary/type/geojson_curah_hujan_kecamatan", ['uses'=>"FrontpageController@gets_geojson_curah_hujan_kecamatan"]);
    $router->get("/region/type/provinsi", ['uses'=>"FrontpageController@gets_region_provinsi"]);
    $router->get("/region/type/kabupaten_kota", ['uses'=>"FrontpageController@gets_region_kabupaten_kota"]);
    $router->get("/region/type/kecamatan", ['uses'=>"FrontpageController@gets_region_kecamatan"]);
    $router->get("/sebaran_opt", ['uses'=>"FrontpageController@gets_sebaran_opt"]);
    $router->get("/sebaran_opt/region", ['uses'=>"FrontpageController@gets_sebaran_opt_region"]);
    $router->get("/region/data/sebaran_opt", ['uses'=>"FrontpageController@gets_region_sebaran_opt"]);
    $router->get("/bantuan_dpi", ['uses'=>"FrontpageController@gets_bantuan_dpi"]);
    $router->get("/bantuan_dpi/region", ['uses'=>"FrontpageController@gets_bantuan_dpi_region"]);
    $router->get("/bantuan_dpi/peta", ['uses'=>"FrontpageController@gets_bantuan_dpi_peta"]);
    //region
    $router->get("/region/type/all", ['uses'=>"FrontpageController@gets_region"]);
    //curah hujan
    $router->get("/curah_hujan", ['uses'=>"FrontpageController@gets_curah_hujan"]);
    $router->get("/curah_hujan/sebaran_opt", ['uses'=>"FrontpageController@gets_curah_hujan_sebaran_opt"]);

});

//test
$router->group(['prefix'=>"/test"], function()use($router){
    $router->get("/json", ['uses'=>"TestController@gets_json"]);
    $router->get("/json/update", ['uses'=>"TestController@update"]);
    $router->get("/json/test_paginate", ['uses'=>"TestController@test_paginate"]);
    $router->get("/json/update_center", ['uses'=>"TestController@update_center"]);
    $router->get("/json/update_center_kecamatan", ['uses'=>"TestController@update_center_kecamatan"]);
    $router->get("/json/get_center", ['uses'=>"TestController@get_center"]);
    $router->get("/json/import_sebaran_opt", ['uses'=>"TestController@import_sebaran_opt"]);
    $router->get("/json/update_sebaran_opt", ['uses'=>"TestController@update_sebaran_opt"]);
    $router->get("/validation", ['uses'=>"TestController@validation"]);
});