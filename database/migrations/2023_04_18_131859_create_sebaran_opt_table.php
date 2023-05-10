<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_sebaran_opt', function (Blueprint $table) {
            $table->id("id_sebaran_opt");
            $table->unsignedInteger("bulan");
            $table->unsignedInteger("tahun");
            $table->text("provinsi");
            $table->text("kab_kota");
            $table->text("komoditas");
            $table->text("opt");
            $table->double("lts_ringan")->default(0);
            $table->double("lts_sedang")->default(0);
            $table->double("lts_berat")->default(0);
            $table->double("sum_lts")->default(0);
            $table->double("lts_puso")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_sebaran_opt');
    }
};
