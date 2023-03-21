<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCertificateTemplateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificate_template_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('certificate_template_id')->references('id')->on('certificate_templates')->index();
            $table->string('language')->index();
            $table->string('background_image')->nullable();
            $table->string('background_image_url')->nullable();
            $table->text('background_image_size')->nullable();
            $table->text('elements')->nullable();
            $table->timestamps();
        });
        Artisan::call('translations:migrate', [
            'model' => 'CertificateTemplate',
        ]);
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn('background_image');
            $table->dropColumn('background_image_url');
            $table->dropColumn('background_image_size');
            $table->dropColumn('elements');
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
        Schema::drop('certificate_template_translations');
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->string('background_image')->nullable();
            $table->string('background_image_url')->nullable();
            $table->text('background_image_size')->nullable();
            $table->text('elements')->nullable();
        });
    }
}
