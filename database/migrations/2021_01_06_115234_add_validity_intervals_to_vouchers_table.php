<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidityIntervalsToVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->integer('validity_interval')->default(0);
            $table->integer('validity_duration')->nullable()->default(null);
        });
        foreach(\App\Models\Voucher::all() as $voucher) {
            if($voucher->validity_months !== null) {
                // Disable timestamps, so we don't change the updated_at column
                $voucher->timestamps = false;
                $voucher->validity_interval = \App\Models\Voucher::INTERVAL_MONTHS;
                $voucher->validity_duration = $voucher->validity_months;
                $voucher->save();
            }
        }
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('validity_months');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->integer('validity_months')->nullable();
        });
        foreach(\App\Models\Voucher::all() as $voucher) {
            if($voucher->validity_duration !== null) {
                // Disable timestamps, so we don't change the updated_at column
                $voucher->timestamps = false;
                // Yes, this isn't correct, but at least we don't loose the data this way
                $voucher->validity_months = $voucher->validity_duration;
                $voucher->save();
            }
        }
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('validity_interval');
            $table->dropColumn('validity_duration');
        });
    }
}
