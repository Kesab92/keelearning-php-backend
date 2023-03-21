<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateContactSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach(\App\Models\App::all() as $app) {
            /** @var \App\Models\App $app */
            $defaultProfile = $app->getDefaultAppProfile();
            $profileSettings = new \App\Services\AppProfileSettings($defaultProfile->id);

            $contactSettings = explode(';', $app->contact_information);

            $profileSettings->setValue('contact_phone', $contactSettings[0]);
            $profileSettings->setValue('contact_email', $contactSettings[1]);
            $profileSettings->setValue('notification_mails', $app->notification_mails);
            $profileSettings->setValue('tos_id', $app->tos_id);
            $profileSettings->setValue('email_terms', $app->terms);
        }

        Schema::table('apps', function (Blueprint $table) {
            $table->dropColumn('contact_information');
            $table->dropColumn('tos_id');
            $table->dropColumn('terms');
            $table->dropColumn('notification_mails');
            $table->dropColumn('default_avatar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->text('contact_information');
            $table->bigInteger('tos_id');
            $table->text('terms');
            $table->string('notification_mails');
            $table->string('default_avatar');
        });
    }
}
