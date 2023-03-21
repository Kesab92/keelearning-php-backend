<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWebinarIdToWebinarParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinar_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('webinar_id')
                ->references('id')
                ->on('webinars')
                ->index('webinar_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webinar_participants', function (Blueprint $table) {
            $table->dropColumn('webinar_id');
        });
    }
}
