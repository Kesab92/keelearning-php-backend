<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateAppLanguagesToAppSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach(\App\Models\App::all() as $app) {
            /** @var App $app */
            $appSettings = new \App\Services\AppSettings($app->id);
            $appSettings->setValue('defaultLanguage', \App\Models\App::getLanguageByIdOld($app->id));
            $appSettings->setValue('languages', json_encode(\App\Models\App::getLanguagesByIdOld($app->id)));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
