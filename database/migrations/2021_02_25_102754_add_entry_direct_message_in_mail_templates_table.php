<?php

use App\Models\MailTemplate;
use Illuminate\Database\Migrations\Migration;

class AddEntryDirectMessageInMailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mailTemplate = new MailTemplate();
        $mailTemplate->app_id = 0;
        $mailTemplate->type = 'DirectMessage';
        $mailTemplate->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $mailTemplate = MailTemplate::where('type', 'DirectMessage');
        $mailTemplate->delete();
    }
}
