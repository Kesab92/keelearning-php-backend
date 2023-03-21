<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('app_id')
                ->references('id')
                ->on('apps')
                ->index('app_id');
            $table->text('tag_ids');
            $table->text('group_ids');
            $table->text('category_ids');
            $table->text('emails');
            $table->string('interval')->default('1m');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reportings');
    }
}
