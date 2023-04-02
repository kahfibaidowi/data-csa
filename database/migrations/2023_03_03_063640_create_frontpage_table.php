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
        Schema::create('tbl_frontpage', function (Blueprint $table) {
            $table->id("id_frontpage");
            $table->unsignedBigInteger("id_user")->nullable()->comment("create by");
            $table->string("type", 200);
            $table->mediumText("data")->comment("json");
            $table->timestamps();
            
            //fk
            $table->foreign("id_user")->references("id_user")->on("tbl_users")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_frontpage');
    }
};
