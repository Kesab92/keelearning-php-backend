<?php

namespace App\Services\AccessLogMeta;

use App\Models\Tag;
use App\Models\User;

class AccessLogAppProfileChanged implements AccessLogMeta
{
    private $appProfileId;
    private $key;
    private $value;

    public function __construct($appProfileId, $key, $value)
    {
        $this->appProfileId = $appProfileId;
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'app_profile_id' => $this->appProfileId,
            'key' => $this->key,
            'value' => $this->value,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.appprofilechanged', [
            'meta' => $meta,
        ]);
    }
}
