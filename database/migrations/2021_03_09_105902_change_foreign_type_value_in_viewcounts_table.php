<?php

use App\Models\Viewcount;
use App\Services\MorphTypes;
use Illuminate\Database\Migrations\Migration;

class ChangeForeignTypeValueInViewcountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Viewcount::where('foreign_type', '=', 'App\Models\News')
            ->update(['foreign_type' => MorphTypes::TYPE_NEWS]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Viewcount::where('foreign_type', '=', MorphTypes::TYPE_NEWS)
            ->update(['foreign_type' => 'App\Models\News']);
    }
}
