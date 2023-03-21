<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AmendAzureVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('azure_videos', function (Blueprint $table) {
            $table->string('job_name', 255)->nullable()->after('job_id');
            $table->string('input_asset_name', 255)->nullable()->after('input_asset_id');
            $table->string('output_asset_name', 255)->nullable()->after('output_asset_id');
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
            $table->dropColumn('job_name');
            $table->dropColumn('input_asset_name');
            $table->dropColumn('output_asset_name');
        });
    }
}
