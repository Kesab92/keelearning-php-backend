<?php

use App\Models\MailTemplate;
use Illuminate\Database\Migrations\Migration;

class AddEntryItemFeedbackInMailTemplatesTable extends Migration
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
        $mailTemplate->type = 'ItemFeedback';
        $mailTemplate->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $mailTemplate = MailTemplate::where('type', 'ItemFeedback');
        $mailTemplate->delete();
    }
}
