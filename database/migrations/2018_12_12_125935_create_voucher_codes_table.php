<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('voucher_id')->references('id')->on('vouchers');
            $table->string('code');
            $table->integer('user_id')->references('id')->on('users')->nullable();
            $table->dateTime('cash_in_date')->nullable();
            $table->timestamps();

            $table->index(['voucher_id', 'user_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_codes');
    }
}
