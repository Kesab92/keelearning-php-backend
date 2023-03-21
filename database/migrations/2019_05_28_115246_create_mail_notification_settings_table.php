<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_notification_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id')
                ->references('id')
                ->on('apps');
            $table->string('mail');
            $table->boolean('deactivatable');
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
        Schema::dropIfExists('mail_notification_settings');
    }
}
