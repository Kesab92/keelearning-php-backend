<?php

namespace App\Services;

use App\Models\App;
use App\Models\AppProfile;
use App\Models\AppProfileSetting;
use App\Models\FrontendTranslation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Color\Hex;

class AppConfigEngine
{
    /**
     * @param string $identifier This is either a hostname or a string consisting of "slug:" and the slug of the app profile
     * @param null|User $user
     * @param boolean $removePrivateSettings
     * @return array
     * @throws \Exception
     */
    public function getConfig($identifier, $user = null, $removePrivateSettings = true)
    {
        $appProfile = $this->getAppProfile($identifier, $user);
        if (! $appProfile) {
            app()->abort(404, 'Could not find app');
        }
        /** @var App $app */
        $app = $appProfile->app;
        $appProfileSettings = new AppProfileSettings($appProfile->id);
        $homeComponentsEngine = new AppProfileHomeComponentsEngine();

        return [
            'id' => $app->id,
            'profile_id' => $appProfile->id,
            'name' => $appProfileSettings->getValue('app_name'),
            'slug' => $appProfileSettings->getValue('slug'),
            'settings' => $this->getSettings($app, $appProfileSettings, $removePrivateSettings),
            'colors' => $this->getColors($appProfileSettings),
            'defaultLanguage' => $app->getLanguage(),
            'languages' => $app->getLanguages(),
            'translations' => $this->getTranslations($appProfile, language($app->id)),
            'translations_language' => language($app->id),
            'home_components' => $homeComponentsEngine->getHomeComponents($appProfile, true, true),
        ];
    }

    public function getWebmanifestFromRequest(Request $request, $appProfile = null)
    {
        $referrer = $request->server('HTTP_REFERER');
        if ($referrer) {
            $hostname = $this->getHostname($referrer);
            if(!$appProfile) {
                $appProfile = $this->getAppProfile($hostname, null);
            }
        } else {
            $hostname = '/';
            if(!$appProfile) {
                $appProfile = App::find(App::ID_KEEUNIT_DEMO)->getDefaultAppProfile();
            }
        }

        return $this->getWebmanifest($appProfile, $hostname);
    }

    public function getWebmanifest(AppProfile $appProfile, $hostname)
    {
        if (! live()) {
            $startUrl = 'http://localhost:5000';
        } else {
            $startUrl = 'https://'.$hostname;
        }

        return [
            'name' => $appProfile->getValue('app_name'),
            'short_name' => $appProfile->getValue('app_name_short'),
            'theme_color' => $appProfile->getValue('color_primary'),
            'background_color' => '#fbfbfe',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'start_url' => $startUrl,
            'scope' => $startUrl,
            'icons' => [
                [
                    'src' => $appProfile->getValue('app_icon'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
            'splash_pages' => null,
        ];
    }

    /**
     * @param string $identifier
     * @param User|null $user
     * @return App|App[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\HigherOrderBuilderProxy|\Illuminate\Database\Eloquent\Model|mixed|null
     */
    public function getAppProfile($identifier, ?User $user)
    {
        if(Str::startsWith($identifier, 'id:')) {
            if(!$user) {
                return null;
            }
            $idPart = substr($identifier, strlen('id:'));
            if(intval($idPart) !== $user->app_id) {
                return null;
            }
            return $user->getAppProfile();
        }
        if(Str::startsWith($identifier, 'slug:')) {
            $slug = substr($identifier,strlen('slug:'));
            if(!$slug) {
                return null;
            }
            $appProfileSetting = AppProfileSetting
                ::where('key', 'slug')
                ->where('value', $slug)
                ->first();
            if(!$appProfileSetting) {
                return null;
            }
            $app = $appProfileSetting->appProfile->app;
            if($user && $user->app_id !== $app->id) {
                return null;
            }
            return $appProfileSetting->appProfile;
        }
        if(Str::startsWith($identifier, 'capacitorid:')) {
            /** @var AppProfileSetting $appProfileSetting */
            $appProfileSetting = AppProfileSetting
                ::whereIn('key', ['ios_app_id', 'android_app_id'])
                ->where('value', substr($identifier,strlen('capacitorid:')))
                ->first();
            if(!$appProfileSetting) {
                return null;
            }
            $app = $appProfileSetting->appProfile->app;
            if($user && $user->app_id !== $app->id) {
                return null;
            }
            return $appProfileSetting->appProfile;
        }
        $subdomain = $this->getSubdomain($identifier);
        $appProfileSetting = AppProfileSetting::query();
        if ($subdomain) {
            $appProfileSetting
                ->where('key', 'subdomain')
                ->where('value', $subdomain);
        } else {
            $appProfileSetting
                ->where('key', 'external_domain')
                ->where('value', $identifier);
        }
        $appProfileSetting = $appProfileSetting->first();
        if(!$appProfileSetting) {
            return null;
        }
        if($user && $user->app_id !== $appProfileSetting->appProfile->app_id) {
            return null;
        }
        return $appProfileSetting->appProfile;
    }

    private function getSubdomain($hostname)
    {
        $domain = str_replace('https://', '', $hostname);
        $expl = explode('.', $domain);
        if(count($expl) === 1) {
            return '';
        }
        if ($expl[count($expl) - 2] === 'keelearning' && $expl[count($expl) - 1] === 'de') {
            array_pop($expl);
            array_pop($expl);

            return implode('.', $expl);
        }

        return '';
    }

    private function getHostname($url)
    {
        return parse_url($url, PHP_URL_HOST);
    }

    private function getSettings($app, $appProfileSettings, $removePrivateSettings = false)
    {
        $settings = AppProfileSettings::$settings;
        $data = [];
        foreach ($settings as $setting => $settingConfig) {
            if ($settingConfig['type'] === 'color') {
                continue;
            }
            if ($removePrivateSettings && isset($settingConfig['private']) && $settingConfig['private'] === true) {
                continue;
            }
            $value = $appProfileSettings->getValue($setting);
            if ($value === '1') {
                $value = true;
            }
            if ($value === '0') {
                $value = false;
            }
            $data[$setting] = $value;
        }

        /**
         * This legacy code is needed for mobile apps and the old PWA versions
         * @deprecated
         */
        if($data['module_quiz_teams']) {
            $data['module_teams'] = $data['module_quiz_teams'];
        }

        return $data;
    }

    private function getColors($appProfileSettings)
    {
        $settings = AppProfileSettings::$settings;
        $colors = [];
        foreach ($settings as $setting => $settingConfig) {
            if ($settingConfig['type'] !== 'color') {
                continue;
            }
            $colorName = str_replace('color_', '', $setting);
            $colorValue = $appProfileSettings->getValue($setting);
            $hex = Hex::fromString($colorValue);
            $rgb = $hex->toRgb();
            $colors[$colorName] = [
                'r' => $rgb->red(),
                'g' => $rgb->green(),
                'b' => $rgb->blue(),
            ];
        }

        return $colors;
    }

    private function getTranslations($appProfile, $language)
    {
        $translations = FrontendTranslation
            ::where('app_profile_id', $appProfile->id)
            ->where('language', $language)
            ->pluck('content', 'key');
        $nestedTranslations = [];
        foreach ($translations as $key=>$content) {
            Arr::set($nestedTranslations, $key, $content);
        }

        return $nestedTranslations;
    }
}
