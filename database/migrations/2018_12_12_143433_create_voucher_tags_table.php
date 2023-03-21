<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('voucher_id')->references('id')->on('vouchers');
            $table->integer('tag_id')->references('id')->on('tags');
            $table->timestamps();

            $table->index(['voucher_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_tags');
    }
}
