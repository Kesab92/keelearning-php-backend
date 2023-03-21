<?php

namespace App\Services;

use App\Models\App;
use App\Models\AppSetting;
use Illuminate\Database\Eloquent\Model;
use Sentry;

/**
 * Class AppSettings
 * Functionality to control app specific settings.
 */
class AppSettings
{
    private $appId;
    private $app;
    private static $_cache = [];

    public static $hideAreas = [
        'hide_competitions'             => 'Gewinnspiele deaktivieren',
        'hide_intercom_chat'            => 'Support-Chat deaktivieren',
        'hide_multiple_questions_types' => 'Alternative Fragetypen deaktivieren',
        'hide_question_attachments'     => 'Fragenanhänge deaktivieren',
        'hide_question_feedback'        => 'Fragen Feedback deaktivieren',
        'hide_stats_quiz_challenge'     => 'Quizchallenge-Statistiken deaktivieren',
        'hide_stats_training'           => 'Training-Statistiken deaktivieren',
        'hide_stats_users'              => 'Benutzer-Statistiken deaktivieren',
        'hide_stats_views'              => 'View-Statistiken deaktivieren',
        'hide_stats_wbt'                => 'WBT-Statistiken deaktivieren',
        'hide_tag_groups'               => 'Tag-Gruppen deaktivieren',
        'hide_user_export'              => 'Benutzerexport deaktivieren',
        'hide_users'                    => 'Benutzerverwaltung deaktivieren',
    ];

    public static $modules = [
        'module_advertisements'      => 'Banner (Anzeigen) aktivieren',
        'module_appointments'        => 'Termine aktivieren',
        'module_bots'                => 'Quiz Bots aktivieren',
        'module_comments'            => 'Kommentare aktivieren',
        'module_competitions'        => 'Gewinnspiele aktivieren',
        'module_courses'             => 'Kurse aktivieren',
        'module_forms'               => 'Formulare aktivieren',
        'module_homepage_components' => 'Homepage-Builder aktivieren',
        'module_index_cards'         => 'Karteikarten aktivieren (veraltet)',
        'module_keywords'            => 'Schlagwörter aktivieren',
        'module_learningmaterials'   => 'Lernmaterialien aktivieren',
        'module_news'                => 'News aktivieren',
        'module_powerlearning'       => 'Powerlearning aktivieren',
        'module_questions'           => 'Fragenpool aktivieren',
        'module_quiz'                => 'Quizmodul aktivieren',
        'module_suggested_questions' => 'User Eingereichte Fragen aktivieren',
        'module_tests'               => 'Tests aktivieren',
        'module_todolists'           => 'Globale Aufgaben aktivieren',
        'module_vouchers'            => 'Vouchers aktivieren',
        'module_webinars'            => 'Webinare aktivieren',
    ];

    // This array defines some hide areas which should be disabled if the corresponding module is not available
    public static $areaModuleMappings = [
        'stats_quiz_challenge' => 'quiz',
        'stats_training'       => 'powerlearning',
        'stats_wbt'            => 'learningmaterials',
    ];

    public static $imports = [
        'import_questions'          => 'Fragen importieren',
        'import_index_cards'        => 'Karteikarten importieren',
        'import_users'              => 'Benutzer importieren',
        'import_users_delete'       => 'Benutzer löschen',
    ];

    public static $miscSettings = [
        'users_choose_categories'                => 'User wählen die Kategorien',
        'no_weekend_grace_period'                => 'Runden-Countdown läuft auch während des Wochenendes ab',
        'bot_game_mails'                         => 'E-Mails werden auch für Bot-Spiele versendet',
        'hide_emails_frontend'                   => 'E-Mail Adressen der Nutzer ausblenden (Frontend)',
        'hide_general_quiz_statistics'           => 'Allgemeines Quiz Ranking deaktivieren',
        'use_subcategory_system'                 => 'Kategorien in Oberkategorien unterteilen',
        'sort_learning_materials_alphabetically' => 'Lernmaterialien alphabetisch sortieren',
        'sort_categories_alphabetically'         => 'Kategorien alphabetisch sortieren',
        'sort_tests_alphabetically'              => 'Tests alphabetisch sortieren',
        'hide_given_test_answers'                => 'Korrekte Test-Antworten ausblenden',
    ];

    public static $superadminSettings = [
        'hide_question_time_setting' => 'Individuelle Zeiten pro Frage deaktivieren',
        'save_user_ip_info'          => 'Herkunftsland der User-IP speichern',
        'show_latex_fields'          => 'LaTeX-Felder anzeigen',
        'wbt_enabled'                => 'WBT-Funktion aktivieren',
        'scorm_wbts_enabled'         => 'SCORM WBTs aktivieren',
        'hide_personal_data'       => 'Keine personenbezogenen Ergebnisse anzeigen',
        'hide_personal_data_for_external_users'  => 'Externe Empfänger - keine personenbezogene Daten senden',
        'hide_emails_backend'        => 'E-Mail Adressen der Nutzer ausblenden (Backend)',
        'has_candy_frontend'         => 'Die App nutzt primär das neue Candy Frontend',
        'has_subpages'               => 'Sub-Seiten aktivieren',
        'has_login_limiations'       => 'Login beschränken können',
    ];

    public function __construct($appId = null)
    {
        $this->appId = $appId;
    }

    /**
     * Returns settings which are available for non superadmins.
     *
     * @return array
     */
    public function publicSettings()
    {
        $allowedSettings = [];
        $allowedSettings = array_merge($allowedSettings, array_keys(self::$miscSettings));

        return $allowedSettings;
    }

    /**
     * Returns settings which are available for the current user.
     *
     * @return array
     */
    public function allowedSettings()
    {
        $allowedSettings = $this->publicSettings();
        if (isSuperadmin()) {
            $allowedSettings = array_merge($allowedSettings, array_keys(self::$superadminSettings));
            $allowedSettings = array_merge($allowedSettings, array_keys(self::$imports));
            $allowedSettings = array_merge($allowedSettings, array_keys(self::$hideAreas));
            $allowedSettings = array_merge($allowedSettings, array_keys(self::$modules));
        }

        return $allowedSettings;
    }

    /**
     * Returns all settings
     *
     * @return array
     */
    public function readonlySettings()
    {
        $allowedSettings = $this->publicSettings();
        $allowedSettings = array_merge($allowedSettings, array_keys(self::$superadminSettings));
        $allowedSettings = array_merge($allowedSettings, array_keys(self::$imports));
        $allowedSettings = array_merge($allowedSettings, array_keys(self::$hideAreas));
        $allowedSettings = array_merge($allowedSettings, array_keys(self::$modules));

        return $allowedSettings;
    }

    public function getAppId()
    {
        if (! $this->appId) {
            $this->appId = appId();
        }

        return $this->appId;
    }

    /**
     * Returns the current app.
     *
     * @return App|Model
     */
    public function getApp()
    {
        if (!$this->app) {
            $this->app = App::find($this->getAppId());
        }
        return $this->app;
    }

    /**
     * Checks if the given area is visible.
     *
     * @param $area
     *
     * @return null
     * @throws \Exception
     */
    public function isBackendVisible($area)
    {
        $hideKey = 'hide_'.$area;
        if (array_key_exists($hideKey, self::$hideAreas) && $this->getValue($hideKey)) {
            return false;
        }

        $moduleArea = $area;
        if (array_key_exists($moduleArea, self::$areaModuleMappings)) {
            $moduleArea = self::$areaModuleMappings[$moduleArea];
        }

        $moduleKey = 'module_'.$moduleArea;
        if (!array_key_exists($moduleKey, self::$modules) && !array_key_exists($hideKey, self::$hideAreas)) {
            // no more silently falling back on true if module to check against doesn't exist
            Sentry::captureMessage('Invalid backend area check: ' . $area);
            return false;
        }
        if (array_key_exists($moduleKey, self::$modules)) {
            return $this->getValue($moduleKey);
        }
        return true;
    }

    /**
     * Returns a specific setting for this app.
     *
     * @param string $key
     * @param bool $noDefault
     * @param bool $castBoolean
     *
     * @return null|mixed
     */
    public function getValue(string $key, bool $castBoolean = false)
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

        return null;
    }

    /**
     * Sets a setting for this app.
     *
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    public function setValue($key, $value)
    {
        $appSetting = AppSetting::firstOrNew(['app_id' => $this->getAppId(), 'key' => $key]);
        $appSetting->value = $value;
        $appSetting->save();

        if (isset(self::$_cache[$this->getAppId()])) {
            self::$_cache[$this->getAppId()][$key] = $value;
        }
    }

    /**
     * Fetches and caches (for this request) all app settings.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getCachedValues()
    {
        if (! isset(self::$_cache[$this->getAppId()])) {
            self::$_cache[$this->getAppId()] = AppSetting::where('app_id', $this->getAppId())
                                      ->pluck('value', 'key');
        }

        return self::$_cache[$this->getAppId()];
    }

    /**
     * Clears the cache for this app.
     */
    public function clearCache()
    {
        if (isset(self::$_cache[$this->getAppId()])) {
            unset(self::$_cache[$this->getAppId()]);
        }
    }

    static public function clearStaticCache()
    {
        self::$_cache = [];
    }
}
