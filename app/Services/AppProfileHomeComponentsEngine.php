<?php

namespace App\Services;

use App\Models\AppProfile;
use App\Models\AppProfileHomeComponent;
use App\Services\AppSettings;

class AppProfileHomeComponentsEngine
{
    public function getDefaultHomeComponents(AppProfile $appProfile)
    {
        return array_map(function ($index, $component) use ($appProfile) {
            $component['app_profile_id'] = $appProfile->id;
            $component['position'] = $index;
            return $component;
        }, array_keys(AppProfileHomeComponent::DEFAULT_COMPONENTS), AppProfileHomeComponent::DEFAULT_COMPONENTS);
    }

    public function getHomeComponents(AppProfile $appProfile, $onlyVisible = false, $withDefaultSettings = false)
    {
        $appSettings = new AppSettings($appProfile->app_id);
        $homeComponents = null;
        if ($appSettings->getValue('module_homepage_components')) {
            $homeComponents = AppProfileHomeComponent::where('app_profile_id', $appProfile->id)
                ->orderBy('position')
                ->get();
        }
        if (!$homeComponents || !$homeComponents->count()) {
            $homeComponents = collect($this->getDefaultHomeComponents($appProfile));
        }
        $homeComponents = $homeComponents->filter(function ($component) use ($appProfile, $appSettings, $onlyVisible) {
            if ($onlyVisible && !$component['visible']) {
                return false;
            }
            $blueprint = AppProfileHomeComponent::BLUEPRINTS[$component['type']];
            // requires a module that is disabled by superadmin?
            if ($blueprint['module'] && !$appSettings->getValue($blueprint['module'])) {
                return false;
            }

            // hides "appMobileInstallation" if the app profile has own app for Android or IOS
            if($component['type'] === 'appmobileinstallation' && ($appProfile->getValue('android_app_id') || $appProfile->getValue('ios_app_id'))) {
                return false;
            }

            return true;
        })->map(function($component) use ($withDefaultSettings) {
            if(!is_array($component)) {
                $component = $component->toArray();
            }

            if(!$withDefaultSettings) {
                return $component;
            }

            $blueprint = AppProfileHomeComponent::BLUEPRINTS[$component['type']];

            if(isset($blueprint['settings'])) {
                foreach ($blueprint['settings'] as $name => $setting) {
                    if(empty($component['settings'])) {
                        $component['settings'] = [];
                    }
                    if(!isset($component['settings'][$name]) && isset($blueprint['settings'][$name]['default'])) {
                        $component['settings'][$name] = $blueprint['settings'][$name]['default'];
                    }
                }
            }
            return $component;
        })->values()->toArray();

        return $homeComponents;
    }

    public function saveHomeComponents(AppProfile $appProfile, Array $requestComponents)
    {
        $uniqueComponents = [];
        $appSettings = new AppSettings($appProfile->app_id);
        $components = collect($requestComponents)->filter(function ($component) use (&$uniqueComponents, $appSettings) {
            if (!isset($component['type'])) {
                return false;
            }
            if (!isset($component['visible'])) {
                return false;
            }
            // unique component that's already in collection?
            if (in_array($component['type'], $uniqueComponents)) {
                return false;
            }
            // type must be valid
            if (!array_key_exists($component['type'], AppProfileHomeComponent::BLUEPRINTS)) {
                return false;
            }

            $blueprint = AppProfileHomeComponent::BLUEPRINTS[$component['type']];

            // requires a module that is disabled by superadmin?
            if ($blueprint['module'] && !$appSettings->getValue($blueprint['module'])) {
                return false;
            }

            if (isset($blueprint['settings'])) {
                $blueprintSettings = collect($blueprint['settings'])->keys();

                $defaultSettings = collect([]);

                foreach ($blueprint['settings'] as $setting => $settingData) {
                    if(isset($settingData['default']) && !isset($component['settings'][$setting])) {
                        $defaultSettings->push($setting);
                    }
                    // custom validation per setting type
                    if(isset($component['settings'][$setting])) {
                        switch ($settingData['type']) {
                            case 'select':
                                $validOptions = collect($settingData['options'])->pluck('value');
                                if (!$validOptions->contains($component['settings'][$setting])) {
                                    return false;
                                }
                                break;
                        }
                    }
                }

                if(isset($component['settings'])) {
                    $componentSettingsWithDefaultSettings = $defaultSettings->concat(array_keys($component['settings']))->unique();
                } else {
                    $componentSettingsWithDefaultSettings = $defaultSettings->unique();
                }

                // if blueprint has settings, those must be supplied
                if (!isset($component['settings']) && $defaultSettings->isEmpty()) {
                    return false;
                }
                // if the settings of component & blueprint don't match, discard
                if ($blueprintSettings->diff($componentSettingsWithDefaultSettings)->count()) {
                    return false;
                }
            }
            if ($blueprint['unique']) {
                $uniqueComponents[] = $component['type'];
            }
            return true;
        });
        if (!$components->where('visible', true)->count()) {
            return false;
        }
        AppProfileHomeComponent::where('app_profile_id', $appProfile->id)
            ->whereNotIn('id', $components->pluck('id'))
            ->delete();
        foreach ($components as $component) {
            if(isset($component['settings'])) {
                $component['settings'] = array_filter($component['settings'], function($item) {
                    return $item !== '';
                });
                if(!count($component['settings'])) {
                    $component['settings'] = null;
                }
            }

            $homeComponent = null;
            if (isset($component['id'])) {
                $homeComponent = AppProfileHomeComponent::where('app_profile_id', $appProfile->id)
                    ->find($component['id']);
            }
            if (!$homeComponent) {
                $homeComponent = new AppProfileHomeComponent();
            }
            $homeComponent->app_profile_id = $appProfile->id;
            $homeComponent->position = (int)$component['position'];
            $homeComponent->type = $component['type'];
            $homeComponent->visible = !!$component['visible'];
            $homeComponent->settings = isset($component['settings']) ? $component['settings'] : null;
            $homeComponent->save();
        }
        return true;
    }
}
