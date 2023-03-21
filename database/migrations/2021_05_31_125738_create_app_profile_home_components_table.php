<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppProfileHomeComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_profile_home_components', function (Blueprint $table) {
            $table->id();
            $table->integer('app_profile_id')->index()->references('id')->on('app_profiles');
            $table->integer('position');
            $table->string('type');
            $table->boolean('visible');
            $table->text('settings')->nullable();
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
        Schema::dropIfExists('app_profile_home_components');
    }
}
