<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAzureVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('azure_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id')->references('id')->on('apps');
            $table->index('app_id');
            $table->integer('progress')->default(0);
            $table->integer('status');
            $table->dateTime('finished_at')->nullable();
            $table->string('job_id')->nullable();
            $table->string('input_asset_id')->nullable();
            $table->string('output_asset_id')->nullable();
            $table->string('streaming_url')->nullable();
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
        Schema::dropIfExists('azure_videos');
    }
}
