<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintfulAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printful_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('address1');
            $table->string('address2')->nullable();
            $table->string('city');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('country_id');
            $table->string('zip');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('type')->default('customer');
            $table->timestamps();

            $table->foreign('state_id')->references('id')->on('printful_states');
            $table->foreign('country_id')->references('id')->on('printful_countries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('printful_addresses');
    }
}
