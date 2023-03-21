<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSambaInviteeIdToWebinarParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinar_participants', function (Blueprint $table) {
            $table->integer('samba_invitee_id');
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
            $table->dropColumn('samba_invitee_id');
        });
    }
}
