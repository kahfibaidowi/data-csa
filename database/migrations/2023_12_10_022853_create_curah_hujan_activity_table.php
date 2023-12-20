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
        Schema::create('tbl_curah_hujan_activity', function (Blueprint $table) {
            $table->id("id_curah_hujan_activity");
            $table->unsignedBigInteger("id_region")->comment("region provinsi/kabupaten kota/kecamatan");
            $table->integer("tahun");
            $table->integer("bulan");
            $table->integer("input_ke");
            $table->double("curah_hujan");
            $table->unsignedBigInteger("id_user")->nullable()->comment("created by");
            $table->text("info_device");
            $table->timestamps();

            //fk/index
            $table->index(["bulan", "input_ke"]);
            $table->foreign("id_region")->references("id_region")->on("tbl_region")->onDelete("cascade");
            $table->foreign("id_user")->references("id_user")->on("tbl_users")->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_curah_hujan_activity');
    }
};
