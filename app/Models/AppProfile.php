<?php

namespace App\Models;

use App\Services\AppProfileSettings;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AppProfile
 *
 * @property int $id
 * @property string $name
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AppProfileSetting[] $settings
 * @property-read int|null $settings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AppProfileTag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfile whereUpdatedAt($value)
 * @mixin IdeHelperAppProfile
 */
class AppProfile extends KeelearningModel
{
    use HasFactory;

    private static $_cache = [];

    const NATIVE_APP_SCHEMAS = [
        'de.sopamo.keeunit.keelearning' => 'keelearning',
    ];

    /**
     * @return BelongsTo
     */
    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function settings()
    {
        return $this->hasMany(AppProfileSetting::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'app_profile_tags')->withTimestamps();
    }

    /**
     * Gets the value of an app profile setting
     *
     * @param string $key
     * @param boolean $noDefault return null when unset instead of default
     * @param boolean $castBoolean cast '1' and '0' to booleans
     * @return mixed
     */
    public function getValue(string $key, bool $noDefault = false, bool $castBoolean = false)
    {
        $values = $this->getCachedValues();
        if (isset($values[$key]) && $values[$key] !== '') {
            // TODO: make default
            if ($castBoolean) {
                if ($values[$key] === '1') {
                    return true;
                }
                if ($values[$key] === '0') {
                    return false;
                }
            }
            return $values[$key];
        }
        if (! $noDefault && isset(AppProfileSettings::$settings[$key])) {
            return AppProfileSettings::$settings[$key]['default'];
        }

        return null;
    }

    public function getDomain()
    {
        if($externalDomain = $this->getValue('external_domain')) {
            return $externalDomain;
        }

        if($subdomain = $this->getValue('subdomain')) {
            return $subdomain . '.keelearning.de';
        }

        return $this->app->app_hosted_at;
    }

    /**
     * Returns if a notification is active.
     * @param string $type
     * @return bool
     */
    public function isActiveNotification(string $type):bool
    {
        return (bool) $this->getValue('notification_'.$type.'_enabled');
    }

    /**
     * Returns if a notification is user manageable.
     * @param string $type
     * @return bool
     */
    public function canNotificationBeDisabledByUser(string $type):bool
    {
        return !!$this->getValue('notification_'.$type.'_user_manageable', false, true);
    }

    /**
     * Clears the cache for this app profile
     * This is mainly needed for horizon, as that's really long running and wouldn't pick up config changes otherwise.
     */
    public function clearCache()
    {
        if (isset(self::$_cache[$this->id])) {
            unset(self::$_cache[$this->id]);
        }
    }

    static public function clearStaticCache()
    {
        self::$_cache = [];
    }

    /**
     * Fetches and caches (for this request) all app profile settings.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    private function getCachedValues()
    {
        if (! isset(self::$_cache[$this->id])) {
            self::$_cache[$this->id] = $this->settings->pluck('value', 'key');
        }

        return self::$_cache[$this->id];
    }

    public function getAppHostedAtAttribute()
    {
        if (env('FORCE_APP_HOSTED_AT')) {
            return env('FORCE_APP_HOSTED_AT');
        }
        if ($this->getValue('external_domain')) {
            return 'https://' . $this->getValue('external_domain');
        }
        if ($this->getValue('subdomain')) {
            return 'https://' . $this->getValue('subdomain') . '.keelearning.de';
        }
        return $this->app->app_hosted_at;
    }

    /**
     * Returns the schema, checking for potential global overrides if native app id given
     *
     * @param string|null $nativeAppId id of native app, or null to ignore overrides
     * @return string returns schema without ://, falling back to the global app keelearning://
     *
     * @throws Exception
     */
    public function getNativeAppSchema(?string $nativeAppId = null): string
    {
        if ($nativeAppId && array_key_exists($nativeAppId, self::NATIVE_APP_SCHEMAS)) {
            return self::NATIVE_APP_SCHEMAS[$nativeAppId];
        }
        if (!$this->getValue('native_app_schema')) {
            throw new Exception('No schema defined for AppProfile #'.$this->id.' '.$nativeAppId);
        }
        return $this->getValue('native_app_schema');
    }

    /**
     * Returns the link to content inside a native app,
     * or the web app if no native app ID given/available.
     *
     * @param string $path Path, with leading slash
     * @param string|null $nativeAppId ID of native app, or null if web app
     * @return string full path including protocol
     */
    public function getAppDeepLink(string $path = '/', ?string $nativeAppId = null): string
    {
        if (!$nativeAppId) {
            return $this->getAppHostedAtAttribute() . $path;
        }
        return $this->getNativeAppSchema($nativeAppId) . ':/' . $path;
    }
}
