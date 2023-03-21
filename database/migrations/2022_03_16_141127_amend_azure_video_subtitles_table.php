<?php

use App\Models\AzureVideo;
use App\Models\AzureVideoSubtitle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmendAzureVideoSubtitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('azure_video_subtitles', function (Blueprint $table) {
            $table
                ->string('azure_video_output_asset_id', 255)
                ->after('id')
                ->index();
        });
        AzureVideoSubtitle::chunk(100, function ($azureVideoSubtitles) {
            foreach ($azureVideoSubtitles as $azureVideoSubtitle) {
                $azureVideo = AzureVideo::find($azureVideoSubtitle->azure_video_id);
                $azureVideoSubtitle->azure_video_output_asset_id = $azureVideo->output_asset_id;
                $azureVideoSubtitle->save();
            }
        });
        Schema::table('azure_video_subtitles', function (Blueprint $table) {
            $table->dropColumn('azure_video_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('azure_video_subtitles', function (Blueprint $table) {
            $table
                ->bigInteger('azure_video_id')
                ->after('id')
                ->references('id')
                ->on('azure_videos')
                ->index();
        });
        AzureVideoSubtitle::chunk(100, function ($azureVideoSubtitles) {
            foreach ($azureVideoSubtitles as $azureVideoSubtitle) {
                // no guarantee we get the "right" azure_video entry,
                // but since the video content is the same, no harm done
                $azureVideo = AzureVideo::where('output_asset_id', $azureVideoSubtitle->output_asset_id)
                    ->first();
                $azureVideoSubtitle->azure_video_id = $azureVideo->id;
                $azureVideoSubtitle->save();
            }
        });
        Schema::table('azure_video_subtitles', function (Blueprint $table) {
            $table->dropColumn('azure_video_output_asset_id');
        });
    }
}
