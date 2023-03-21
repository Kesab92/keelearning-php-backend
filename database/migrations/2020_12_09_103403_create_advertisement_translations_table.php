<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_translations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('advertisement_id')->index();
            $table->string('language')->index();
            $table->text('description')->nullable();
            $table->string('link', 1023)->nullable();
            $table->string('rectangle_image_url')->nullable();
            $table->string('leaderboard_image_url')->nullable();
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
        Schema::dropIfExists('advertisement_translations');
    }
}
