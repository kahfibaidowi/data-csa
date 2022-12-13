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
        Schema::create('tbl_region', function (Blueprint $table) {
            $table->id("id_region");
            $table->unsignedBigInteger("nested")->nullable();
            $table->text("type");
            $table->text("region");
            $table->text("data");
            $table->longText("geo_json");
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
        Schema::dropIfExists('tbl_region');
    }
};
