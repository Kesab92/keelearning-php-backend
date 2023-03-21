<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateSmtpSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $keys = [
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_email',
            'smtp_encryption',
        ];
        foreach(\App\Models\App::all() as $app) {
            $settings = new \App\Services\AppSettings($app->id);
            $defaultProfile = $app->getDefaultAppProfile();
            $profileSettings = new \App\Services\AppProfileSettings($defaultProfile->id);
            foreach($keys as $key) {
                if($value = $settings->getValue($key)) {
                    $profileSettings->setValue($key, $value);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
