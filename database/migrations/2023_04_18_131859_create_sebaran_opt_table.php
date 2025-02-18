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
            $table->unsignedBigInteger("id_region")->nullable()->comment("kabupaten/kota")->nullable();
            $table->unsignedInteger("bulan")->nullable();
            $table->unsignedInteger("tahun")->nullable();
            $table->string("periode")->nullable();
            $table->string("kategori")->nullable();
            $table->string("komoditas")->nullable();
            $table->string("jenis_varietas")->nullable();
            $table->string("satuan")->nullable();
            $table->text("opt")->nullable();
            $table->double("lts_ringan")->nullable();
            $table->double("lts_sedang")->nullable();
            $table->double("lts_berat")->nullable();
            $table->double("lts_puso")->nullable();
            $table->double("lks_ringan")->nullable();
            $table->double("lks_sedang")->nullable();
            $table->double("lks_berat")->nullable();
            $table->double("lks_puso")->nullable();
            $table->double("lp_pemusnahan")->nullable();
            $table->double("lp_pestisida_kimia")->nullable();
            $table->double("lp_cara_lain")->nullable();
            $table->double("lp_agens_hayati")->nullable();
            $table->double("sum_lts")->nullable();
            $table->double("sum_lks")->nullable();
            $table->double("sum_lp")->nullable();
            $table->timestamps();

            //fk
            $table->foreign("id_region")->references("id_region")->on("tbl_region")->onDelete("cascade");
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
