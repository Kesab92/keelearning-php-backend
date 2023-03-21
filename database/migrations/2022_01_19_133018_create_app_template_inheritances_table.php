<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppTemplateInheritancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_template_inheritances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_id')->references('id')->on('apps')->index();
            $table->unsignedBigInteger('child_id')->references('id')->on('apps')->index();
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
        Schema::dropIfExists('app_template_inheritances');
    }
}
