<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultToAppProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->index();
        });
        DB::table('app_profiles')->update(['is_default' => 1]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_profiles', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
}
