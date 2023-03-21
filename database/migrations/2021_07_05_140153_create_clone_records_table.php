<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCloneRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clone_records', function (Blueprint $table) {
            $table->id();
            $table->string('class');
            $table->bigInteger('source_id');
            $table->bigInteger('target_app_id');
            $table->bigInteger('target_id');
            $table->timestamps();
            $table->index(['class', 'source_id', 'target_app_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clone_records');
    }
}
