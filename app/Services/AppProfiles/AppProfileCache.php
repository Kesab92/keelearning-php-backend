<?php

namespace App\Services\AppProfiles;

use App\Models\AppProfile;
use Illuminate\Support\Collection;

class AppProfileCache
{
    private static ?Collection $appProfiles = null;

    private static function addAppProfilesToCache(int $appId) {
        $appProfiles = AppProfile
            ::where('app_id', $appId)
            ->with('tags')
            ->orderBy('is_default')
            ->orderBy('id')
            ->get();

        self::$appProfiles->put($appId, $appProfiles);
    }

    public static function getAppProfiles(int $appId):Collection {
        if(!self::$appProfiles) {
            self::$appProfiles = new Collection();
        }

        if(!self::$appProfiles->has($appId)) {
            self::addAppProfilesToCache($appId);
        }

        return self::$appProfiles->get($appId);
    }

    static public function clearStaticCache()
    {
        self::$appProfiles = new Collection();
    }
}
