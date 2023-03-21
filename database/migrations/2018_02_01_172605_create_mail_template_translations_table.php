<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailTemplateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_template_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mail_template_id')->references('id')->on('mail_templates');
            $table->string('language');
            $table->text('title');
            $table->text('body');
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'MailTemplate',
        ]);
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('body');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APP_ENV') !== 'testing') {
            die('Hold up, friend! You will need to manually roll back this migration or you lose a lot of data.');
        }
        Schema::drop('mail_template_translations');
        Schema::table('mail_templates', function (Blueprint $table) {
            $table->text('title');
            $table->text('body');
        });
    }
}
