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
        Schema::create('tbl_curah_hujan', function (Blueprint $table) {
            $table->id("id_curah_hujan");
            $table->unsignedBigInteger("id_region")->comment("region provinsi/kabupaten kota/kecamatan");
            $table->integer("tahun");
            $table->integer("bulan");
            $table->double("curah_hujan");
            $table->double("curah_hujan_normal");
            $table->text("sifat");
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
        Schema::dropIfExists('tbl_curah_hujan');
    }
};
