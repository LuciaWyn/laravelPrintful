<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrintfulFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printful_files', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default("default");
            $table->string('url')->nullable();
            $table->string('hash')->nullable();
            $table->string('filename');
            $table->string('mime_type');
            $table->integer('size');
            $table->integer('width');
            $table->integer('height');
            $table->integer('dpi');
            $table->string('status');
            $table->integer('created');
            $table->string('thumbnail_url');
            $table->string('preview_url');
            $table->boolean('visible');
            $table->unsignedBigInteger('variant_id');
            $table->timestamps();

            $table->foreign('variant_id')->references('id')->on('printful_variants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('printful_files');
    }
}
