<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrivacyNoteConfirmationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('privacy_note_confirmations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->references('id')->on('users')->index();
            $table->unsignedInteger('accepted_version');
            $table->timestamps();
            $table->index(['user_id', 'accepted_version']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('privacy_note_confirmations');
    }
}
