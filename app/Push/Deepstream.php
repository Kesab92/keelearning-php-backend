<?php

namespace App\Push;

use App\Jobs\SendDeepstreamEvent;
use App\Models\App;
use App\Push\Deepstream\DeepstreamClient;
use Config;

class Deepstream
{
    private $client;
    private $app;
    private $forceSync;

    public function __construct(App $app = null, bool $forceSync = false)
    {
        $this->app = $app;
        $this->client = self::getClient();
        $this->forceSync = $forceSync;
    }

    /**
     * Sends an event via deepstream.
     *
     * @param      $event
     * @param null $data
     *
     * @throws \Exception
     */
    public function sendEvent($event, $data = null)
    {
        if (! $this->app->hasDeepstream()) {
            return;
        }
        if ($this->forceSync || app()->runningInConsole()) {
            SendDeepstreamEvent::dispatchSync($event, $data, $this->app);
        } else {
            SendDeepstreamEvent::dispatchAfterResponse($event, $data, $this->app);
        }
    }

    /**
     * Gets the currently active users.
     */
    public function getPresence()
    {
        return $this->client->getPresence();
    }

    /**
     * Checks that deepstream can send events.
     *
     * @return bool
     */
    public function healthcheck()
    {
        try {
            return $this->client->emitEvent('healthcheck', 1);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getClient()
    {
        return new DeepstreamClient(Config::get('services.deepstream.url'), [
            'authData' => [
                'username' => 0,
                'password' => Config::get('services.deepstream.token'),
            ],
        ]);
    }
}
