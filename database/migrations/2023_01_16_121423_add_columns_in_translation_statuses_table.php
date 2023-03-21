<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInTranslationStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('translation_statuses', function (Blueprint $table) {
            $table->unsignedInteger('last_updated_by_id')->nullable();
            $table->boolean('is_autotranslated')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('translation_statuses', function (Blueprint $table) {
            $table->dropColumn('last_updated_by_id');
            $table->dropColumn('is_autotranslated');
        });
    }
}
