<?php

use App\Models\Viewcount;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateViewcountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('viewcounts', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->index();
            $table->index(['foreign_id', 'foreign_type']);
        });
        foreach (Viewcount::all() as $viewcount) {
            $i = 0;
            $newEntries = [];
            while ($i < $viewcount->views) {
                $i++;
                $newViewcount = $viewcount->replicate();
                $newViewcount->user_id = null;
                $newViewcount->created_at = $viewcount->created_at;
                $newViewcount->updated_at = $viewcount->updated_at;
                $newEntries[] = $newViewcount->toArray();
                if ($i % 500 === 0) {
                    Viewcount::insert($newEntries);
                    $newEntries = [];
                }
            }
            Viewcount::insert($newEntries);
            $viewcount->delete();
        }
        Schema::table('viewcounts', function (Blueprint $table) {
            $table->dropColumn('views');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('viewcounts', function (Blueprint $table) {
            $table->unsignedBigInteger('views')->default(0);
            $table->dropColumn('user_id');
        });
    }
}
