<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintfulCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printful_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            //left nullable because of API
            $table->unsignedBigInteger('region_id')->default(null)->nullable();
            $table->timestamps();

            $table->foreign('region_id')->references('id')->on('printful_regions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('printful_countries');
    }
}
