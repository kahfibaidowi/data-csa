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
        Schema::create('tbl_bantuan_dpi', function (Blueprint $table) {
            $table->id("id_bantuan_dpi");
            $table->unsignedBigInteger("id_region")->comment("kecamatan");
            $table->integer("tahun");
            $table->text("jenis_bantuan");
            $table->text("kelompok_tani");
            $table->text("pj_kelompok_tani");
            $table->timestamps();

            //fk/index
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
        Schema::dropIfExists('tbl_bantuan_dpi');
    }
};
