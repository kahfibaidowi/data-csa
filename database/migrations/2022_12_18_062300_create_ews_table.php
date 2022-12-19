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
        Schema::create('tbl_ews', function (Blueprint $table) {
            $table->id("id_ews");
            $table->unsignedBigInteger("id_region")->comment("region provinsi/kabupaten kota/kecamatan");
            $table->text("type");
            $table->integer("tahun");
            $table->integer("bulan");
            $table->double("curah_hujan");
            $table->text("opt_utama");
            $table->double("produksi");
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
        Schema::dropIfExists('tbl_ews');
    }
};
