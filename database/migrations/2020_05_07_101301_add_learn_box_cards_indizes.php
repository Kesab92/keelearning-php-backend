<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLearnBoxCardsIndizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('learn_box_cards', function (Blueprint $table) {
            $table->index(['foreign_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learn_box_cards', function (Blueprint $table) {
            $table->dropIndex('learn_box_cards_foreign_id_type_index');
        });
    }
}
