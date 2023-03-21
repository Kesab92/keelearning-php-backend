<?php

use App\Models\App;
use App\Models\User;
use Hackzilla\PasswordGenerator\Generator\RequirementPasswordGenerator;
use Hackzilla\PasswordGenerator\RandomGenerator\Php7RandomGenerator;
use Illuminate\Support\Str;
use StupidPass\StupidPass;
use Tymon\JWTAuth\Providers\JWT\Namshi;

/**
 * Returns the authenticated users
 * ATTENTION: Currently only works for the API.
 *
 * @return mixed|User|null
 */
function user()
{
    try {
        $user = auth('api')->user();
        if (! $user) {
            return null;
        }
    } catch (\Exception $e) {
        try {
            // try alternative secret
            app()->extend('tymon.jwt.provider.jwt.namshi', function (Namshi $namshi) {
                $namshi->setSecret(env('JWT_SECRET_ALTERNATIVE'));

                return $namshi;
            });
            $user = auth('api')->user();
            if (! $user) {
                return null;
            }
        } catch (\Exception $e) {
            Sentry::captureException($e);

            return null;
        }
    }

    return $user;
}

/**
 * Returns the id of the app which is selected in the backend
 * Usually this is just the app id of the user, but superadmins can choose another one.
 *
 * @return int
 * @throws Exception
 */
function appId()
{
    // Check for api authentication
    if (auth('api')->check()) {
        return auth('api')->user()->app_id;
    }

    if (! Auth::check()) {
        throw new Exception('You are accessing appId() without being logged in!');
    }
    if (isSuperAdmin() && Cookie::has('appid') && intval(Cookie::get('appid')) > 0) {
        return intval(Cookie::get('appid'));
    } else {
        return Auth::user()->app_id;
    }
}

function isSuperAdmin()
{
    return Auth::user()->isSuperAdmin();
}

/**
 * Returns the default language of the selected app.
 *
 * @return string
 */
function defaultAppLanguage($appId = null)
{
    if(Config::get('app.force_default_language')) {
        return Config::get('app.force_default_language');
    }
    if (is_null($appId)) {
        $appId = appId();
    }
    if (!$appId) {
        return 'de';
    }
    return App::getLanguageById($appId);
}

/**
 * Returns all languages supported by the app.
 *
 * @return array
 * @throws Exception
 */
function appLanguages($appId = null)
{
    if (is_null($appId)) {
        $appId = appId();
    }
    // global fallback
    if (!$appId) {
        return ['de'];
    }

    return App::getLanguagesById($appId);
}


/**
 * Returns the current language requested by the user.
 *
 * @param null $appId
 * @return string
 * @throws Exception
 */
function language($appId = null)
{
    $lang = null;
    if (Config::get('app.force_language')) {
        // We're skipping the check if this is a valid language for the app id,
        // because otherwise we'd also need to know the appId for the appLanguages() call down below.
        return Config::get('app.force_language');
    } elseif (request()->input('lang')) {
        $lang = request()->input('lang');
    } elseif (request()->header('X-LANGUAGE')) {
        $lang = request()->header('X-LANGUAGE');
    } elseif (Cookie::has('lang')) {
        // $lang = Cookie::get('lang');
        // Setting the language via cookie is disabled, because translations are now handled in the relaunch
    } else {
        try {
            $apiUser = user();
            if ($apiUser) {
                $lang = $apiUser->language;
            }
        } catch (\Exception $e) {
        }
    }
    if (! in_array($lang, appLanguages($appId))) {
        $lang = defaultAppLanguage($appId);
    }

    return $lang;
}

/**
 * Checks whether we are running in production mode or not.
 *
 * @return bool
 */
function live()
{
    return app()->env == 'production';
}

/**
 * The function decodes a base 64 string and writes it into a file.
 *
 * @param $base64String
 * @param $outputPath
 * @return mixed
 */
function base64_to_file($base64String)
{
    $outputPath = tempnam(sys_get_temp_dir(), 'base64_to_file');
    $fileHandler = fopen($outputPath, 'wb');
    $data = explode(',', $base64String);
    fwrite($fileHandler, base64_decode($data[1]));
    fclose($fileHandler);

    return $outputPath;
}

/**
 * The function creates a random password with the length given as input parameter.
 *
 * @param int $length
 * @return string
 */
function randomPassword($length = 12)
{
    $generator = new RequirementPasswordGenerator();

    $generator
        ->setRandomGenerator(new Php7RandomGenerator())
        ->setOptionValue(RequirementPasswordGenerator::OPTION_UPPER_CASE, false)
        ->setOptionValue(RequirementPasswordGenerator::OPTION_LOWER_CASE, true)
        ->setOptionValue(RequirementPasswordGenerator::OPTION_NUMBERS, true)
        ->setOptionValue(RequirementPasswordGenerator::OPTION_SYMBOLS, false)
        ->setMinimumCount(RequirementPasswordGenerator::OPTION_LOWER_CASE, 2)
        ->setMinimumCount(RequirementPasswordGenerator::OPTION_NUMBERS, 2)
        ->setLength($length);

    return $generator->generatePassword();
}

/**
 * Checks if a given password is secure enough.
 *
 * @param string $password
 * @param array $badwords Array of environmental phrases, like usernames or app names
 * @return array
 */
function validatePassword(string $password, array $badwords = [])
{
    $hardlang = [
        'length' => __('errors.stupidpass_length'),
        'upper'  => __('errors.stupidpass_upper'),
        'lower'  => __('errors.stupidpass_lower'),
        'numeric'=> __('errors.stupidpass_numeric'),
        'special'=> __('errors.stupidpass_special'),
        'onlynumeric' => __('errors.stupidpass_onlynumeric'),
        'common' => __('errors.stupidpass_common'),
        'environ'=> __('errors.stupidpass_environ'),
    ];
    $environmental = collect($badwords)
        ->push('quiz')
        ->filter(function ($entry) {
            return is_string($entry);
        })
        ->map(function ($entry) {
            $entries = explode(' ', $entry);
            $entries[] = $entry;
            return $entries;
        })
        ->flatten()
        ->filter(function ($entry) {
            return strlen($entry) > 3;
        })
        ->map(function ($entry) {
            // the delimiter is necessary because $badwords can contain '/' in the string
            return preg_quote($entry, '/');
        })
        ->toArray();
    $options = ['disable' => []];

    $stupidPass = new StupidPass(40, $environmental, storage_path('dictionaries/common_passwords.txt'), $hardlang, $options);

    return [
        'valid' => $stupidPass->validate($password),
        'result' => $stupidPass,
    ];
}

/**
 * Get the path to the backend.
 *
 * @return string
 */
function backendPath()
{
    if (live()) {
        return 'https://admin.keelearning.de';
    } else {
        return 'http://qa.test';
    }
}

/**
 * Converts links in plaintext to <a> tags
 *
 * @param string $text
 * @return string
 */
function plaintextLinksToTags(string $text): string
{
    return preg_replace('/(http[s]{0,1}\:\/\/\S{4,})/ims', '<a href="$1">$1</a> ', $text);
}

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function boxPercentagesToCssGradient($boxes, $usePowerLearning = false)
{
    if ($usePowerLearning) {
        $boxColors = [
            '1' => 'rgb(140,35,65)',
            '2' => 'rgb(245,135,30)',
            '3' => 'rgb(250,185,50)',
            '4' => 'rgb(140,190,50)',
        ];
    } else {
        $boxColors = [
            '1' => 'rgb(216,27,0)',
            '2' => 'rgb(232,104,86)',
            '3' => 'rgb(239,131,57)',
            '4' => 'rgb(242,158,99)',
            '5' => 'rgb(41,213,250)',
        ];
    }
    $gradientParts = [];
    $currentPosition = 0;
    foreach ($boxColors as $box => $color) {
        if ($boxes['box_'.$box.'_percent']) {
            $gradientParts[] = $color.' '.$currentPosition.'%';
            $currentPosition += round($boxes['box_'.$box.'_percent'] * 100, 2);
            $gradientParts[] = $color.' '.$currentPosition.'%';
        }
    }
    $css = 'background-image:linear-gradient(to left,'.implode(',', $gradientParts).');';

    return $css;
}

/**
 * Lower bound of Wilson score confidence interval for Bernoulli parameter
 * tl;dr: better sorting
 * http://www.evanmiller.org/how-not-to-sort-by-average-rating.html
 * z score = 1.96 -> 95% confidence.
 *
 * @param int   $positive   Number of positive ratings/wins/etc…
 * @param int   $total      Total number
 * @return string
 */
function calculateScore($positive, $total)
{
    $z = 1.96;
    $ratio = $positive / $total;

    return (
        $ratio + $z * $z / (2 * $total)
        - $z * sqrt(($ratio * (1 - $ratio)
        + $z * $z / (4 * $total)) / $total)) / (1 + $z * $z / $total
    );
}

/**
 * The function generates a filename of the pattern `$oldfilename-$uuid.$extension`.
 *
 * @param $file file resource
 * @param $noExtension
 * @return string
 */
function createFilename($file, $noExtension = false)
{
    $fileSlug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
    $fileAppendix = '-'.$file->hashName();
    if ($noExtension) {
        $fileAppendix = pathinfo($fileAppendix, PATHINFO_FILENAME);
    } elseif (!$file->extension()) {
        // if the MIME type isn't found by symfony/mime, hashName returns an extensionless filename
        $fileAppendix = $fileAppendix . '.' . $file->getClientOriginalExtension();
    }
    // truncate slug so filename has a max total length of 255 chars
    $filename = substr($fileSlug, 0, (255 - strlen($fileAppendix))).$fileAppendix;

    return $filename;
}

/**
 * The function generates a filename of the pattern `$oldfilename-$uuid.$extension`.
 *
 * @param $fileName filename as string
 * @param $noExtension
 * @return string
 */
function createFilenameFromString($fileName, $noExtension = false)
{
    $fileSlug = Str::slug(pathinfo($fileName, PATHINFO_FILENAME));
    $fileAppendix = '-'.Str::random(40) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
    if ($noExtension) {
        $fileAppendix = pathinfo($fileAppendix, PATHINFO_FILENAME);
    }
    // truncate slug so filename has a max total length of 255 chars
    $filename = substr($fileSlug, 0, (255 - strlen($fileAppendix))).$fileAppendix;

    return $filename;
}

function caseSensitiveSlug($string, $separator = '-', $language = 'de') {
    $string = $language ? Str::ascii($string, $language) : $string;

    // Convert all dashes/underscores into separator
    $flip = $separator === '-' ? '_' : '-';

    $string = preg_replace('!['.preg_quote($flip).']+!u', $separator, $string);

    // Replace @ with the word 'at'
    $string = str_replace('@', $separator.'at'.$separator, $string);

    // Remove all characters that are not the separator, letters, numbers, or whitespace.
    $string = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', $string);

    // Replace all separator characters and whitespace by a single separator
    $string = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $string);

    return utrim($string, $separator);
}

/**
 * Prepends the slash to a path if not already there.
 *
 * @param string $path
 * @return string
 */
// TODO: remove after upgrade to new frontend
function convertPathToLegacy($path)
{
    if (! $path) {
        return null;
    }
    if (substr($path, 0, 1) === '/') {
        return $path;
    }

    return '/'.$path;
}

/**
 * Given a string like '#1234' returns '1234'.
 *
 * @param string $input
 * @return string
 */
function extractHashtagNumber($input)
{
    return preg_replace('/^#(\d+)$/', '$1', $input);
}

/**
 * Formats an URL to an uploaded resource, depending on environment
 * and frontend API version. Can probably be removed once all frontends are
 * at least on 2.0.0 and all assets are in Azure.
 *
 * @deprecated
 * @param string $url
 * @param string|null $apiVersion
 * @return string
 */
function formatAssetURL(?string $url, ?string $apiVersion = null): ?string
{
    if (!$url) {
        return $url;
    }
    if ($apiVersion === null) {
        $apiVersion = request()->header('X-API-VERSION', '1.0.0');
    }
    $parsed = parse_url($url);
    $path = $parsed['path'];
    $cleanedPath = preg_replace('~^/storage/~', '', $path);
    $cleanedPath = preg_replace('~^/~', '', $cleanedPath);

    if (version_compare($apiVersion, '2.0.0', '>=')) {
        // New frontends
        if (!live()) {
            if (!Str::startsWith($url, 'http')) {
                return 'http://qa.test/'.$url;
            } else {
                return $url;
            }
        }
        if (Str::startsWith($url, 'https://'.env('AZURE_STORAGE_NAME'))) {
            return $url;
        }

        return 'https://'.env('AZURE_STORAGE_NAME').'.blob.core.windows.net/'.env('AZURE_STORAGE_CONTAINER').'/'.$cleanedPath;
    } else {
        // Old frontends
        return '/'.$cleanedPath;
    }
}

/**
 * Gets the path to a resource in Azure when given an URL
 *
 * @param string $url
 * @return string
 */
function getBlobStorageFilename(string $url): string
{
    $parsed = parse_url($url);
    $path = $parsed['path'];
    $cleanedPath = preg_replace('~^/'.env('AZURE_STORAGE_CONTAINER').'/~', '', $path);
    $cleanedPath = preg_replace('~^/~', '', $cleanedPath);
    return $cleanedPath;
}

/**
 * Checks if a given mimetype/extension combo is a zip file
 *
 * @param string $mimeType
 * @param string $extension
 * @return boolean
 */
function isZipFile(string $mimeType, string $extension) : bool
{
    return in_array(strtolower($mimeType), ['application/x-zip-compressed', 'application/zip'])
        && $extension === 'zip';
}

/**
 * Checks if a given mimetype is a supported image
 *
 * @param string $mimeType
 * @return boolean
 */
function isImage(string $mimeType): bool
{
    $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/svg+xml',
        ];
    return in_array($mimeType, $allowedMimeTypes);
}

/**
 * Checks if a given mime type is a supported audio file
 *
 * @param string $mineType
 * @return boolean
 */
function isAudio(string $mimeType): bool
{
    $allowedMimeTypes = [
        'audio/mpeg',
        'audio/mp3',
    ];
    return in_array($mimeType, $allowedMimeTypes);
}

/**
 * Returns an array of valid emails from a string of comma and/or semicolon separated emails,
 * discarding all invalid entries.
 *
 * @param string $emails
 * @return array
 */
function parseEmails(string $emails): array
{
    return collect(preg_split('/[;,]/', $emails))
        ->map(function ($email) {
            return utrim($email);
        })
        ->filter(function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        })
        ->values()
        ->toArray();
}

/**
 * Strips control characters from strings.
 * Mainly used so phpoffice/phpspreadsheet doesn't break.
 *
 * @param string $text
 * @return string
 */
function stripControlCharacters($text): string
{
    if(!$text) {
        return '';
    }
    return preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
}

/**
 * Removes everything except alphanumeric chars,
 * replacing - and _ with spaces.
 *
 * @param string $input
 * @return string
 */
function alphaNumericOnly(string $input): string
{
    $output = Str::ascii($input, 'de');
    $output = str_replace(['-', '_'], ' ', $output);
    $output = preg_replace('/[^a-zA-Z 0-9]+/', '', $output);
    return $output;
}

/**
 * Creates a dummy mail address
 *
 * @return string something like `nomail123@sopamo.de`
 */
function createDummyMail(): string
{
    return 'nomail' . uniqid() . '@sopamo.de';
}

/**
 * Checks if a given mail address is a dummy mail.
 *
 * @param string $mail
 * @return boolean
 */
// CLONED TO STATS SERVER
function isDummyMail(string $mail): bool
{
    return Str::startsWith($mail, 'nomail') && Str::endsWith($mail, '@sopamo.de');
}

/**
 * Escapes user supplied input for usage in an SQL LIKE query
 * This expects a default escape char of `\`
 *
 * @param string $input
 * @return string
 */
function escapeLikeInput(string $input): string
{
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $input);
}

/**
 * Checks if the currentVersion is at least the minimum version
 *
 * @param string $minimumVersion
 * @param string|null $currentVersion
 * @return bool
 */
function hasAPIVersion(string $minimumVersion, string $currentVersion = null): bool {
    if(!$currentVersion) {
        $currentVersion = request()->header('X-API-VERSION', '1.0.0');
    }
    return version_compare($currentVersion, $minimumVersion, '>=');
}

/**
 * Works basically like the built-in `trim` function,
 * but will trim special whitespaces like the non-breaking-space &nbsp;
 * Temporarily disabled special handling, because it lead to issues
 *
 * @param string|null $input
 * @return string
 */
function utrim(?string $input = ''): string
{
    if (!$input) {
        return '';
    }
    return trim($input);
}

/**
 * Works basically like the built-in `rtrim` function,
 * but will trim special whitespaces like the non-breaking-space &nbsp;
 *
 * @param string|null $input
 * @return string
 */
function urtrim(?string $input = ''): string
{
    if (!$input) {
        return '';
    }
    return rtrim($input, " \t\n\r\0\x0B\xC2\xA0");
}
