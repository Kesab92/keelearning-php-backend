<?php

namespace App\Services\AccessLogMeta;

interface AccessLogMeta
{
    /**
     * @return array
     */
    public function getMeta();

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta);
}
