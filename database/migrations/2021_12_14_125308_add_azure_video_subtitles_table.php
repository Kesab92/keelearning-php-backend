<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAzureVideoSubtitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('azure_video_subtitles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('azure_video_id')->references('id')->on('azure_videos')->index();
            $table->string('language');
            $table->integer('progress')->default(0);
            $table->integer('status')->default(0);
            $table->dateTime('finished_at')->nullable();
            $table->text('asset_name');
            $table->text('job_name');
            $table->string('streaming_url', 255)->nullable();
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
        Schema::dropIfExists('azure_video_subtitles');
    }
}
