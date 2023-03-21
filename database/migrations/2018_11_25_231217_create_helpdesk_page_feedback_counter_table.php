<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelpdeskPageFeedbackCounterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('helpdesk_page_feedbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('page_id')->references('id')->on('helpdesk_pages');
            $table->integer('user_id')->references('id')->on('users');
            $table->timestamps();

            $table->unique(['page_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('helpdesk_page_feedbacks');
    }
}
