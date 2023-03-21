<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrontendTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frontend_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id');
            $table->string('language');
            $table->string('key');
            $table->text('content');
            $table->timestamps();
            $table->index(['app_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('frontend_translations');
    }
}
