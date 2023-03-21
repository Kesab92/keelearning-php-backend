<?php

namespace App\Services;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;

class IPGeolocation
{
    protected static $_instance = null;
    private $reader;

    protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
            self::$_instance->reader = new Reader(storage_path('app/GeoLite2-Country.mmdb'));
        }

        return self::$_instance;
    }

    public function isoCode($ip)
    {
        try {
            $record = $this->reader->country($ip);
        } catch (AddressNotFoundException $e) {
            return '??';
        }

        return $record->country->isoCode;
    }
}
