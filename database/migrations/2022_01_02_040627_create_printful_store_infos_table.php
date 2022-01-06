<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintfulStoreInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printful_store_infos', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->string('name');
            $table->string('website')->nullable();
            $table->unsignedBigInteger('return_address_id')->nullable();
            $table->unsignedBigInteger('billing_address_id');
            $table->string('currency');
            $table->string('type');
            $table->integer('created');

            $table->string('packaging_email')->nullable();
            $table->string('packaging_phone')->nullable();
            $table->string('packaging_message')->nullable();
            $table->string('packaging_logo_url')->nullable();
            $table->timestamps();

            $table->foreign('return_address_id')->references('id')->on('printful_addresses');
            $table->foreign('billing_address_id')->references('id')->on('printful_addresses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('printful_store_infos');
    }
}
