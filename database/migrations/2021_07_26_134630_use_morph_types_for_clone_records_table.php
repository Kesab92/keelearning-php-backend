<?php

use App\Models\CloneRecord;
use App\Services\MorphTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UseMorphTypesForCloneRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clone_records', function (Blueprint $table) {
            $table->integer('type')->after('id');
        });
        $records = CloneRecord::get();
        foreach ($records as $record) {
            $record->type = MorphTypes::MAPPING[$record->class];
            $record->save();
        }
        Schema::table('clone_records', function (Blueprint $table) {
            $table->dropIndex(['class', 'source_id', 'target_app_id']);
            $table->dropColumn('class');
            $table->index(['type', 'source_id', 'target_app_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clone_records', function (Blueprint $table) {
            $table->string('class')->after('id');
        });
        $records = CloneRecord::get();
        $mapping = array_flip(MorphTypes::MAPPING);
        foreach ($records as $record) {
            $record->class = $mapping[$record->type];
            $record->save();
        }
        Schema::table('clone_records', function (Blueprint $table) {
            $table->dropIndex(['type', 'source_id', 'target_app_id']);
            $table->dropColumn('type');
            $table->index(['class', 'source_id', 'target_app_id']);
        });
    }
}
