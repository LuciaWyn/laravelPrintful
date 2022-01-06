<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintfulVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printful_variants', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('printful_id');
            $table->bigInteger('printful_variant_id');
            $table->string('external_id');
            $table->bigInteger('sync_product_id');
            $table->string('name');
            $table->boolean('synced')->default(true);
            $table->double('retail_price', 10, 2);
            $table->string('currency');
            $table->string('thumbnail_url')->nullable();
            $table->string('preview_url')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('printful_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('printful_variants');
    }
}
