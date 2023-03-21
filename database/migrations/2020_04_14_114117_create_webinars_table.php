<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('app_id')->references('id')->on('apps');
            $table->string('topic');
            $table->text('description')->nullable();
            $table->datetime('starts_at');
            $table->integer('duration_minutes')->nullable();
            $table->boolean('send_reminder')->default(false);
            $table->datetime('reminder_sent_at')->nullable();
            $table->boolean('show_recordings')->default(false);
            $table->timestamps();
            $table->index('app_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webinars');
    }
}
