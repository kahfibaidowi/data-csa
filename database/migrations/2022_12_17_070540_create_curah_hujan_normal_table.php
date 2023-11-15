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
        Schema::create('tbl_curah_hujan_normal', function (Blueprint $table) {
            $table->id("id_curah_hujan_normal");
            $table->unsignedBigInteger("id_region")->comment("region provinsi/kabupaten kota/kecamatan");
            $table->integer("bulan");
            $table->integer("input_ke");
            $table->double("curah_hujan_normal");
            $table->timestamps();

            //fk/index
            $table->index(["bulan", "input_ke"]);
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
        Schema::dropIfExists('tbl_curah_hujan_normal');
    }
};
