<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddImagemapToIndexcardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('index_cards', function (Blueprint $table) {
            $table->string('json')->nullable();
            $table->string('type')->default('standard');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('index_cards', function (Blueprint $table) {
            $table->dropColumn('json');
            $table->dropColumn('type');
        });
    }
}
