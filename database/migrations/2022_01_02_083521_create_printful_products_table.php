<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintfulProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printful_products', function (Blueprint $table) {
            $table->id();
            $table->integer('printful_id');
            $table->string('external_id');
            $table->string('name');
            $table->integer('variants');
            $table->integer('synced');
            $table->string('thumbnail_url')->nullable();
            $table->boolean('is_ignored')->nullable();
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
        Schema::dropIfExists('printful_products');
    }
}
