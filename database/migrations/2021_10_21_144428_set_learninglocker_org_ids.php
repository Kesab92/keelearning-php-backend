<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetLearninglockerOrgIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $organisationURL = env('LEARNING_LOCKER_API') . '/organisation';

        $client = new GuzzleHttp\Client();
        foreach(\App\Models\App::whereNotNull('xapi_token')->get() as $app) {
            if(!$app->xapi_token) {
                continue;
            }
            $res = $client->request('GET', $organisationURL, [
                'headers' => [
                    'Authorization' => $app->xapi_token,
                    'Content-Type' => 'application/json',
                ],
            ]);
            $result = json_decode($res->getBody()->getContents(), true);
            if(count($result) != 1) {
                throw new Exception('Invalid result count for app ' . $app->name);
            }
            $app->learninglocker_id = $result[0]['_id'];
            $app->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
