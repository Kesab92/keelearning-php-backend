<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubtitlesLanguageFieldToAzureVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('azure_videos', function (Blueprint $table) {
            $table->string('subtitles_language')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('azure_videos', function (Blueprint $table) {
            $table->dropColumn('subtitles_language');
        });
    }
}
