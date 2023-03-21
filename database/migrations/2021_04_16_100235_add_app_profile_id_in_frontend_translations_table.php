<?php

use App\Models\FrontendTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\App;

class AddAppProfileIdInFrontendTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frontend_translations', function (Blueprint $table) {
            $table->bigInteger('app_profile_id')->index();
        });

        $apps = App::all();
        foreach($apps as $app) {
            $translations = FrontendTranslation::where('app_id', $app->id)->get();
            $defaultProfileId = null;

            foreach($app->profiles as $profile) {
                if($profile->is_default) {
                    $defaultProfileId = $profile->id;
                }
            }

            if($defaultProfileId !== null) {
                foreach ($translations as $translation) {
                    $translation->app_profile_id = $defaultProfileId;
                    $translation->save();
                }
            }
        }

        Schema::table('frontend_translations', function (Blueprint $table) {
            $table->dropColumn('app_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frontend_translations', function (Blueprint $table) {
            $table->dropColumn('app_profile_id');
        });
    }
}
