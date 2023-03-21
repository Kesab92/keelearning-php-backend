<?php

namespace App\Services;

class Emoji
{
    const OFFSET = 127397;

    private static function unichr($u)
    {
        return mb_convert_encoding('&#'.intval($u).';', 'UTF-8', 'HTML-ENTITIES');
    }

    public static function getCountryFlag($isoCode)
    {
        if (! $isoCode) {
            return '❔';
        }
        if ($isoCode == '??') {
            return '👽';
        }
        $isoCode = strtoupper($isoCode);
        $emoji = '';
        $emoji .= self::unichr(self::OFFSET + ord($isoCode[0]));
        $emoji .= self::unichr(self::OFFSET + ord($isoCode[1]));

        return $emoji;
    }

    public static function getLanguageFlag($lang)
    {
        switch ($lang) {
            case 'en':
                return self::getCountryFlag('US');
        }

        return self::getCountryFlag($lang);
    }
}
