<?php

use App\Models\App;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CreateUserMetafieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_metafields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->references('id')->on('users')->index();
            $table->string('key', 255)->index();
            $table->text('value');
            $table->timestamps();
        });

        $users = User::whereNotNull('meta')->get();

        foreach ($users as $user) {
            $oldMetadata = json_decode($user->meta, true);
            // sometimes there is, for some unknown reason,
            // the string `null` instead of a null value in the field
            if (!is_array($oldMetadata)) {
                continue;
            }
            $metafields = [];
            $timestamp = Carbon::now();
            foreach ($oldMetadata as $key => $value) {
                $metafields[] = [
                    'user_id' => $user->id,
                    'key' => $key,
                    'value' => $value,
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ];
            }
            DB::table('user_metafields')->insert($metafields);
        }

        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('meta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->text('meta')->nullable();
        });
        $users = User::whereHas('metafields')->with('metafields')->get();
        foreach ($users as $user) {
            $metafields = $user->metafields->pluck('value', 'key');
            $user->meta = json_encode($metafields, JSON_UNESCAPED_UNICODE);
            $user->save();
        }
        Schema::dropIfExists('user_metafields');
    }
}
