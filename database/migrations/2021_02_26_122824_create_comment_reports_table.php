<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comment_reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('comment_id')->index();
            $table->bigInteger('reporter_id')->index();
            $table->integer('reason');
            $table->text('reason_explanation')->nullable();
            $table->integer('status')->index();
            $table->text('status_explanation')->nullable();
            $table->bigInteger('status_manager_id')->nullable();
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
        Schema::dropIfExists('comment_reports');
    }
}
