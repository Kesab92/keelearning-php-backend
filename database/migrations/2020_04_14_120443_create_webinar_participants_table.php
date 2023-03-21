<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinar_participants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')
                ->references('id')
                ->on('users')
                ->nullable();
            $table->unsignedBigInteger('webinar_additional_user_id')
                ->references('id')
                ->on('webinar_additional_users')
                ->nullable();
            $table->string('join_link');
            $table->timestamps();
            $table->index('user_id');
            $table->index('webinar_additional_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webinar_participants');
    }
}
