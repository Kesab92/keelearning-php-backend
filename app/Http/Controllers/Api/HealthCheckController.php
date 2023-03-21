<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Push\Deepstream;
use Cache;
use Config;
use Response;

class HealthCheckController extends Controller
{
    public function healthcheck()
    {
        $database = $this->checkDatabase();
        $redis = $this->checkRedis();
        $deepstream = $this->checkDeepstream();
        $xapi = $this->checkXApi();
        $up = $database && $redis && $deepstream && $xapi;

        return Response::json([
            'database' => $database,
            'redis' => $redis,
            'deepstream' => $deepstream,
            'xapi' => $xapi,
        ], $up ? 200 : 500);
    }

    /**
     * Checks the database connection.
     *
     * @return bool
     */
    private function checkDatabase()
    {
        try {
            return App::count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Checks the redis connection.
     *
     * @return bool
     */
    private function checkRedis()
    {
        try {
            $value = microtime();
            Cache::put('healthcheck', $value, 1);

            return Cache::get('healthcheck') === $value;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Checks the deepstream connection.
     *
     * @return bool
     */
    private function checkDeepstream()
    {
        if (! $this->checkDeepstreamHealthcheck()) {
            return false;
        }
        ob_start();
        try {
            /** @var Deepstream $deepstream */
            $deepstream = new Deepstream();
            $up = $deepstream->healthcheck();
            if ($up) {
                $presence = $deepstream->getPresence();
                if (is_array($presence) && count($presence) === 0) {
                    // No one is currently connected, but deepstream works fine.
                    return true;
                }
                ob_clean();
                // Return the connection count
                return count($presence);
            }
            ob_clean();

            return false;
        } catch (\Exception $e) {
            ob_clean();

            return false;
        }
    }

    /**
     * Checks the internal deepstream healthcheck.
     */
    private function checkDeepstreamHealthcheck()
    {
        ob_start();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, Config::get('services.deepstream.url') . '/health-check');
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        ob_clean();

        return $info['http_code'] === 200;
    }

    private function checkXApi()
    {
        $xapiHealthcheckURL = env('XAPI_URL') . '/about';
        ob_start();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $xapiHealthcheckURL);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        ob_clean();

        return $info['http_code'] === 200;
    }
}
