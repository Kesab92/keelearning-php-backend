<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarAdditionalUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinar_additional_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('webinar_id')->references('id')->on('webinars');
            $table->unsignedBigInteger('user_id')->references('id')->on('users')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->integer('role')->default(2);
            $table->timestamps();
            $table->index('webinar_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webinar_additional_users');
    }
}
