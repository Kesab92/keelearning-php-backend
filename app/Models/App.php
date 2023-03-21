<?php

namespace App\Models;

use App\Models\Courses\Course;
use App\Services\AppSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

/**
 * App\Models\App.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users
 * @property-read \Illuminate\Database\Eloquent\Collection|Category[] $categories
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $rounds_per_game
 * @property int $answers_per_question
 * @property int $questions_per_round
 * @property int $tos_id
 * @property string $default_avatar
 * @property string $app_hosted_at
 * @property int $samba_id
 * @property string $samba_token
 * @property string $support_phone_number
 * @property-read \Illuminate\Database\Eloquent\Collection|Game[] $games
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereRoundsPerGame($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereAnswersPerQuestion($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereQuestionsPerRound($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereAppHostedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereTosId($value)
 * @mixin \Eloquent
 * @property string $terms
 * @property string $contact_information Has to be saved in the following format: "$phone;$email"
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereTerms($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\App whereContactInformation($value)
 * @property string $notification_mails
 * @property string|null $xapi_token
 * @property string $internal_notes
 * @property int|null $user_licences
 * @property-read int|null $categories_count
 * @property-read int|null $games_count
 * @property-read mixed $views
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Page[] $pages
 * @property-read int|null $pages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TagGroup[] $tagGroups
 * @property-read int|null $tag_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Viewcount[] $viewcounts
 * @property-read int|null $viewcounts_count
 * @method static \Illuminate\Database\Eloquent\Builder|App newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|App newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|App query()
 * @method static \Illuminate\Database\Eloquent\Builder|App whereDefaultAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereInternalNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereNotificationMails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereSambaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereSambaToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereSupportPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereUserLicences($value)
 * @method static \Illuminate\Database\Eloquent\Builder|App whereXapiToken($value)
 * @mixin IdeHelperApp
 */
class App extends KeelearningModel
{
    use HasFactory;
    // for dashboard/homepage
    use \App\Traits\Views;

    protected $hidden = [
        'internal_notes',
        'user_licences',
    ];

    private static $cacheDefaultAppProfile = [];

    const ID_WUESTENROT = 1;
    const ID_KEEUNIT_DEMO = 2;
    const ID_WEBDEV_QUIZ = 3;
    const ID_EASYCREDIT = 4;
    const ID_DEUTSCHKURS_MEDIZIN = 5;
    const ID_ZFZ = 6;
    const ID_WUERTTEMBERGISCHE = 7;
    const ID_MAINZ = 8;
    const ID_WOHNDARLEHEN = 9;
    const ID_FORD = 10;
    const ID_SCHWAEBISCH_HALL = 11;
    const ID_HS_GEISENHEIM = 12;
    const ID_LINGOMINT = 13;
    const ID_GENOAKADEMIE = 14;
    const ID_OPENGRID = 15;
    const ID_UNI_WEIMAR = 16;
    const ID_M2 = 17;
    const ID_BAYER = 18;
    const ID_CURATOR = 19;
    const ID_WIKA = 20;
    const ID_RAIFFEISEN = 21;
    const ID_YOURFIRM = 22;
    const ID_HEIDELBERG = 23;
    const ID_THM = 24;
    const ID_APOBANK = 26;
    const ID_TALENT_THINKING = 27;
    const ID_NEXUS_KIS = 28;
    const ID_NEXUS_INTERN = 29;
    const ID_SIGNIA_PRO = 30;
    const ID_DGQ = 31;
    const ID_IHK = 32;
    const ID_VOLKSBANKEN_RAIFFEISENBANKEN = 33;
    const ID_CLARIFY = 34;
    const ID_OLYMPUS = 35;
    const ID_TSK = 36;
    const ID_DMI = 37;
    const ID_KEEUNIT_HR = 38;
    const ID_ERFSTADT = 39;
    const ID_DECISIO = 40;
    const ID_VILLEROY_BOCH = 41;
    const ID_JOCHEN_SCHWEIZER = 42;
    const ID_HNO = 43;
    const ID_ARAG = 44;
    const ID_MONEYCOASTER = 45;
    const ID_WICHTEL_WISSEN = 46;
    const ID_CARGO_BULL = 47;
    const ID_TXP_ACADEMY = 48;
    const ID_HORMOSAN = 49;
    const ID_STAYATHOME = 50;
    const ID_SUTTER = 51;
    const ID_FKZ = 52;
    const ID_GREEN_CARE = 53;
    const ID_SCHIRRMACHER = 54;
    const ID_CLIO = 55;
    const ID_ILEARN = 56;
    const ID_HASCO = 57;
    const ID_VKBILDUNG = 58;
    const ID_KEELEARNING_KURSE = 59;
    const ID_DD_TEAM = 60;
    const ID_BALCONY = 61;
    const ID_ORL = 62;
    const ID_EGN = 63;
    const ID_WENTZEL_DR = 64;
    const ID_MERCURI = 65;
    const ID_KAMBECK = 66;
    const ID_RESMEDIA = 67;
    const ID_REINHOLD = 68;
    const ID_EMEURER = 69;
    const ID_KABS = 70;
    const ID_OCHAIRSYSTEMS = 71;
    const ID_BB_SAMSUNG = 72;
    const ID_NOVAHEAL = 73;
    const ID_AWO_BREMERHAVEN = 74;
    const ID_SANDBOX = 75;
    const ID_SANDBOX3 = 76;
    const ID_TK = 77;
    const ID_RAVATI = 78;
    const ID_VFTC = 79;
    const ID_PERBILITY = 80;
    const ID_GERMANSTUDIOS = 81;
    const ID_RAUMEDIC = 82;
    const ID_SANDBOX2 = 83;
    const ID_BPMO = 84;
    const ID_ECOTEL = 85;
    const ID_ACANDIS = 86;
    const ID_ATESTEO = 87;
    const ID_TEAMWILLE = 88;
    const ID_MUNICH_EMERGENCY_MEDICAL_SERVICES = 89;
    const ID_REBEQ = 90;
    const ID_DVELOP = 91;
    const ID_VZ = 92;
    const ID_WMF = 93;
    const ID_BOLAB = 94;
    const ID_DIV = 95;
    const ID_DC = 96;
    const ID_DAF = 97;
    const ID_SANDBOX5 = 98;
    const ID_SANDBOX6 = 99;
    const ID_UCKERMARKER = 100;
    const ID_ISLAMKOLLEG = 101;
    const ID_LUSINI = 102;
    const ID_BLUME2000 = 103;
    const ID_KEELEARNING_TEMPLATES = 104;
    const ID_ITSC = 105;
    const ID_LEA = 106;
    const ID_HAMBURGER_WASSERWERKE = 107;
    const ID_ALLISON = 108;
    const ID_FIT_FOOD_BOX = 109;
    const ID_SWISSCOM = 110;
    const ID_QVNIA = 111;
    const ID_WR_ACADEMY = 112;
    const ID_FI_TS = 113;
    const ID_FIT_FOR_TRADE = 114;
    const ID_MARKAS = 115;
    const ID_DVELOP_AG = 116;
    const ID_HAENEL = 117;
    const ID_SUPERADMIN_VORLAGEN = 118;
    const ID_PLAYGROUND = 119;
    const ID_GREEN_CARE_DEMO = 120;
    const ID_TEACH_ME_PRO = 121;
    const ID_BOGDOL = 122;
    const ID_SMART_UP_NRW = 123;
    const ID_EITCO = 124;
    const ID_TFK_ACADEMY = 125;
    const ID_ZUDUBI = 126;
    const ID_PERSOLOG = 127;
    const ID_OREX = 128;
    const ID_TEN_PLUS_TWO = 129;
    const ID_MIKROBIOM = 130;
    const ID_KREIS_LIPPE = 131;
    const ID_ELEARNING_VG_LW = 132;
    const ID_SANDBOX9 = 133;
    const ID_K2_LEGAL = 134;
    const ID_LSWB_EDUCATION = 135;
    const ID_CYPRESS = 136;
    const ID_REHAPLUS = 137;
    const ID_UNIKLINIK_ROSTOCK = 138;
    const ID_OBER = 139;
    const ID_GO_CAMPUS = 140;
    const ID_GA_LEMPLATTFORM = 141;
    const ID_STRIEGA = 142;
    const ID_MS_DIRECT_ACADEMY = 143;
    const ID_CARBONI = 144;
    const ID_BROT_UND_SINNE = 145;
    const ID_BOCKHOLT = 146;
    const ID_SANDBOX10 = 147;
    const ID_BABTEC = 148;
    const ID_THERAPEUTIKUM_BARSSEL = 149;
    const ID_SANDBOX11 = 150;
    const ID_SANDBOX12 = 151;
    const ID_SANDBOX13 = 152;
    const ID_SANDBOX14 = 153;
    const ID_DZ4_GMBH = 154;
    const ID_WEBER_HOLDING = 155;
    const ID_ZEBRA_LERN = 156;
    const ID_MEINESTADT_LERN = 157;
    const ID_SANDBOX15 = 158;
    const ID_SANDBOX16 = 159;
    const ID_SANDBOX17 = 160;
    const ID_SANDBOX18 = 161;
    const ID_SANDBOX19 = 162;
    const ID_SANDBOX20 = 163;
    const ID_TOACADEMY = 164;

    // DECISIO
    const DATA_DECISIO = [
        'guest_tag_id' => 983,
    ];

    // ALLISON
    const DATA_ALLISON = [
        'guest_tag_id' => 3708,
    ];

    // WICHTEL WISSEN aka BABILOU
    const DATA_WICHTEL_WISSEN = [
        'guest_tag_id' => 5314,
    ];

    const LANGUAGES = [
        self::ID_KEEUNIT_DEMO => [
            'de',
            'en',
            'fr',
        ],
        self::ID_KEEUNIT_HR => [
            'de',
            'en',
            'it',
            'jp',
            'pl',
        ],
        self::ID_WIKA => [
            'de',
            'en',
        ],
        self::ID_BAYER => [
            'en',
            'de',
        ],
        self::ID_YOURFIRM => [
            'de',
            'en',
        ],
        self::ID_OLYMPUS => [
            'de',
            'en',
        ],
        self::ID_SIGNIA_PRO => [
            'de',
            'en',
        ],
        self::ID_VILLEROY_BOCH => [
            'de',
            'en',
            'fr',
        ],
        self::ID_CARGO_BULL => [
            'de',
            'en',
            'es',
            'it',
            'fr',
            'ru',
        ],
        self::ID_TXP_ACADEMY => [
            'de',
            'en',
        ],
        self::ID_GREEN_CARE => [
            'de',
            'en',
            'fr',
            'it',
            'nl',
            'ro',
            'ru',
            'sr',
            'tr',
        ],
        self::ID_ILEARN => [
            'de',
            'en',
            'pl',
        ],
        self::ID_HASCO => [
            'de',
            'en',
        ],
        self::ID_KEELEARNING_KURSE => [
            'de',
            'en',
        ],
        self::ID_DD_TEAM => [
            'de',
            'en',
        ],
        self::ID_MERCURI => [
            'de',
            'en',
        ],
        self::ID_KAMBECK => [
            'de',
            'en',
            'fr',
        ],
        self::ID_RESMEDIA => [
            'de_formal',
            'en',
        ],
        self::ID_BB_SAMSUNG => [
            'de',
            'fr',
            'it',
        ],
        self::ID_SANDBOX => [
            'de',
            'en',
        ],
        self::ID_SANDBOX3 => [
            'de',
            'en',
        ],
        self::ID_GERMANSTUDIOS => [
            'de',
            'en',
            'zh',
        ],
        self::ID_RAUMEDIC => [
            'de',
            'en',
        ],
        self::ID_SANDBOX2 => [
            'de',
            'en',
        ],
        self::ID_BPMO => [
            'de_formal',
            'en',
        ],
        self::ID_ACANDIS => [
            'de',
            'en',
        ],
        self::ID_TEAMWILLE => [
            'de',
            'en',
        ],
        self::ID_WMF => [
            'de_formal',
            'en',
            'ru',
            'pl',
            'es',
            'tr',
            'zh',
            'it',
            'fr',
            'jp',
            'nl',
            'bg',
            'cs',
        ],
        self::ID_BOLAB => [
            'de',
            'en',
        ],
        self::ID_SANDBOX5 => [
            'de',
            'en',
            'fr',
            'es',
        ],
        self::ID_SANDBOX6 => [
            'de',
            'de_formal',
            'en',
            'fr',
            'es',
        ],
        self::ID_KEELEARNING_TEMPLATES => [
            'de',
            'de_formal',
            'bg',
            'cs',
            'en',
            'pl',
            'it',
            'fr',
            'es',
            'pt',
            'tr',
            'zh',
            'jp',
            'ru',
        ],
        self::ID_SWISSCOM => [
            'de',
            'fr',
            'it',
        ],
        self::ID_MARKAS => [
            'de',
            'en',
            'fr',
            'it',
            'nl',
            'ro',
            'ru',
            'sr',
            'tr',
        ],
        self::ID_DVELOP => [
            'de',
            'de_formal',
        ],
        self::ID_DVELOP_AG => [
            'de',
            'de_formal',
            'en',
        ],
        self::ID_HAENEL => [
            'de',
            'en',
        ],
        self::ID_SUPERADMIN_VORLAGEN => [
            'de',
            'de_formal',
            'al',
            'bg',
            'cs',
            'en',
            'es',
            'fr',
            'hr',
            'hu',
            'it',
            'jp',
            'nl',
            'no',
            'pl',
            'pt',
            'ro',
            'ru',
            'sr',
            'tr',
            'zh',
        ],
        self::ID_PLAYGROUND => [
            'de',
            'en',
        ],
        self::ID_GREEN_CARE_DEMO => [
            'de',
            'en',
            'fr',
            'it',
            'nl',
            'ro',
            'ru',
            'sr',
            'tr',
        ],
        self::ID_LUSINI => [
            'de',
            'en',
            'es',
            'fr',
            'it',
        ],
        self::ID_VZ => [
            'de',
            'fr',
        ],
        self::ID_PERSOLOG => [
            'de',
            'en',
            'no',
        ],
        self::ID_CYPRESS => [
            'de',
            'en',
            'fr',
        ],
        self::ID_MS_DIRECT_ACADEMY => [
            'de',
            'al',
            'hr',
            'fr',
            'it',
            'ro',
            'sr',
            'tr',
        ],
        self::ID_CARBONI => [
            'de',
            'en',
            'fr',
        ],
        self::ID_BOCKHOLT => [
            'de',
            'al',
            'bg',
            'cs',
            'en',
            'es',
            'fr',
            'hr',
            'it',
            'jp',
            'nl',
            'no',
            'pl',
            'pt',
            'ro',
            'ru',
            'sr',
            'tr',
            'zh',
        ],
        self::ID_SANDBOX10 => [
            'de_formal',
            'en',
            'fr',
        ],
        self::ID_SANDBOX11 => [
            'de',
            'en',
            'fr',
        ],
        self::ID_SANDBOX12 => [
            'de',
            'en',
            'fr',
        ],
        self::ID_SANDBOX13 => [
            'de',
            'en',
            'fr',
        ],
        self::ID_SANDBOX14 => [
            'de',
            'en',
            'fr',
        ],
        self::ID_WEBER_HOLDING => [
            'de',
            'en',
            'ru',
            'pl',
            'ru',
            'tr',
        ],
        self::ID_TOACADEMY => [
            'de',
            'en',
        ],
    ];

    const SLUGS = [
        self::ID_WUESTENROT => 'wuestenrot',
        self::ID_KEEUNIT_DEMO => 'demo',
        self::ID_WEBDEV_QUIZ => 'webdev',
        self::ID_EASYCREDIT => 'easycredit',
        self::ID_DEUTSCHKURS_MEDIZIN => 'demedizin',
        self::ID_ZFZ => 'zfz',
        self::ID_WUERTTEMBERGISCHE => 'wuerttemberg',
        self::ID_MAINZ => 'mainz',
        self::ID_WOHNDARLEHEN => 'wohndarlehen',
        self::ID_FORD => 'ford',
        self::ID_SCHWAEBISCH_HALL => 'schwaebisch_hall',
        self::ID_HS_GEISENHEIM => 'hgu',
        self::ID_LINGOMINT => 'lingo',
        self::ID_GENOAKADEMIE => 'genoapp',
        self::ID_OPENGRID => 'opengrid',
        self::ID_UNI_WEIMAR => 'uni_weimar',
        self::ID_M2 => 'm2',
        self::ID_BAYER => 'bayer',
        self::ID_CURATOR => 'curator',
        self::ID_NEXUS_KIS => 'nexus_kis',
        self::ID_WIKA => 'wika',
        self::ID_RAIFFEISEN => 'raiffeisen',
        self::ID_YOURFIRM => 'yourfirm',
        self::ID_HEIDELBERG => 'heidelberg',
        self::ID_THM => 'thm',
        self::ID_APOBANK => 'apobank',
        self::ID_TALENT_THINKING => 'talent_thinking',
        self::ID_NEXUS_INTERN => 'nexusintern',
        self::ID_SIGNIA_PRO => 'signia-pro',
        self::ID_DGQ => 'dgq',
        self::ID_IHK => 'ihk',
        self::ID_VOLKSBANKEN_RAIFFEISENBANKEN => 'vr',
        self::ID_CLARIFY => 'clarify',
        self::ID_OLYMPUS => 'olympus',
        self::ID_TSK => 'tsk',
        self::ID_DMI => 'dmi',
        self::ID_KEEUNIT_HR => 'keeunit_hr',
        self::ID_ERFSTADT => 'erfstadt_klinikum',
        self::ID_DECISIO => 'decisio',
        self::ID_VILLEROY_BOCH => 'villeroy_boch',
        self::ID_JOCHEN_SCHWEIZER => 'jochen_schweizer',
        self::ID_HNO => 'hno',
        self::ID_ARAG => 'arag',
        self::ID_MONEYCOASTER => 'moneycoaster',
        self::ID_WICHTEL_WISSEN => 'wichtel_wissen', // aka Babilou
        self::ID_CARGO_BULL => 'cargo_bull',
        self::ID_TXP_ACADEMY => 'txp_academy',
        self::ID_HORMOSAN => 'hormosan',
        self::ID_STAYATHOME => 'stayathome',
        self::ID_SUTTER => 'slm',
        self::ID_FKZ => 'fkz',
        self::ID_GREEN_CARE => 'green_care',
        self::ID_SCHIRRMACHER => 'schirrmacher',
        self::ID_CLIO => 'clio',
        self::ID_ILEARN => 'ilearn',
        self::ID_HASCO => 'hasco',
        self::ID_VKBILDUNG => 'vkbildung',
        self::ID_KEELEARNING_KURSE => 'keelearning_kurse',
        self::ID_DD_TEAM => 'dd_team',
        self::ID_BALCONY => 'balcony',
        self::ID_ORL => 'orl',
        self::ID_EGN => 'egn',
        self::ID_WENTZEL_DR => 'wentzel_dr',
        self::ID_MERCURI => 'mercuri',
        self::ID_KAMBECK => 'kambeck',
        self::ID_RESMEDIA => 'resmedia',
        self::ID_REINHOLD => 'reinhold',
        self::ID_EMEURER => 'emeurer',
        self::ID_KABS => 'kabs',
        self::ID_OCHAIRSYSTEMS => 'oc_hairsystems',
        self::ID_BB_SAMSUNG => 'bb_samsung',
        self::ID_NOVAHEAL => 'novaheal',
        self::ID_AWO_BREMERHAVEN => 'awo_bremerhaven',
        self::ID_SANDBOX => 'sandbox',
        self::ID_SANDBOX3 => 'sandbox3',
        self::ID_TK => 'tklearn',
        self::ID_RAVATI => 'ravati',
        self::ID_VFTC => 'vftc',
        self::ID_PERBILITY => 'perbility',
        self::ID_GERMANSTUDIOS => 'germanstudios',
        self::ID_RAUMEDIC => 'raumedic',
        self::ID_SANDBOX2 => 'sandbox2',
        self::ID_BPMO => 'bpmo',
        self::ID_ECOTEL => 'ecotel',
        self::ID_ACANDIS => 'acandis',
        self::ID_ATESTEO => 'atesteo',
        self::ID_TEAMWILLE => 'teamwille',
        self::ID_MUNICH_EMERGENCY_MEDICAL_SERVICES => 'rettungsdienstschule-muenchen',
        self::ID_REBEQ => 'rebeq',
        self::ID_DVELOP => 'dvelop',
        self::ID_VZ => 'vz',
        self::ID_WMF => 'wmf',
        self::ID_BOLAB => 'bolab',
        self::ID_DIV => 'div',
        self::ID_DC => 'dc',
        self::ID_DAF => 'daf',
        self::ID_SANDBOX5 => 'sandbox5',
        self::ID_SANDBOX6 => 'sandbox6',
        self::ID_UCKERMARKER => 'uckermarkmilch',
        self::ID_ISLAMKOLLEG => 'islamkolleg',
        self::ID_LUSINI => 'lusini',
        self::ID_BLUME2000 => 'blume2000',
        self::ID_KEELEARNING_TEMPLATES => 'keelearning-templates',
        self::ID_ITSC => 'itsc',
        self::ID_LEA => 'lea',
        self::ID_HAMBURGER_WASSERWERKE => 'hamburger-wasserwerke',
        self::ID_ALLISON => 'allison',
        self::ID_FIT_FOOD_BOX => 'fit-food-box',
        self::ID_SWISSCOM => 'swisscom',
        self::ID_QVNIA => 'qvnia',
        self::ID_WR_ACADEMY => 'wr-academy',
        self::ID_FI_TS => 'fi-ts',
        self::ID_FIT_FOR_TRADE => 'fit-for-trade',
        self::ID_MARKAS => 'markas',
        self::ID_DVELOP_AG => 'dvelop-ag',
        self::ID_HAENEL => 'haenel',
        self::ID_SUPERADMIN_VORLAGEN => 'superadmin-vorlagen',
        self::ID_PLAYGROUND => 'playground',
        self::ID_GREEN_CARE_DEMO => 'green_care_demo',
        self::ID_TEACH_ME_PRO => 'techmepro',
        self::ID_BOGDOL => 'bogdol',
        self::ID_SMART_UP_NRW => 'smart-up-nrw',
        self::ID_EITCO => 'eitco',
        self::ID_TFK_ACADEMY => 'tfk-academy',
        self::ID_ZUDUBI => 'zudubi',
        self::ID_PERSOLOG => 'persolog',
        self::ID_OREX => 'orex',
        self::ID_TEN_PLUS_TWO => 'ten_plus_two',
        self::ID_MIKROBIOM => 'mikrobiom',
        self::ID_KREIS_LIPPE => 'kreis-lippe',
        self::ID_ELEARNING_VG_LW => 'elearning-vg-lw',
        self::ID_SANDBOX9 => 'sandbox9',
        self::ID_K2_LEGAL => 'k2-legal',
        self::ID_LSWB_EDUCATION => 'lswb-education',
        self::ID_CYPRESS => 'cypress',
        self::ID_REHAPLUS => 'rehaplus',
        self::ID_UNIKLINIK_ROSTOCK => 'uniklinik-rostock',
        self::ID_OBER => 'oberunternehmensgruppe',
        self::ID_GO_CAMPUS => 'gocampus',
        self::ID_GA_LEMPLATTFORM => 'galernplattform',
        self::ID_STRIEGA => 'striega',
        self::ID_MS_DIRECT_ACADEMY => 'ms-direct',
        self::ID_CARBONI => 'carboni',
        self::ID_BROT_UND_SINNE => 'brotundsinne',
        self::ID_BOCKHOLT => 'bockholt',
        self::ID_SANDBOX10 => 'sandbox10',
        self::ID_BABTEC => 'babtec',
        self::ID_THERAPEUTIKUM_BARSSEL => 'therapeutikum-barssel',
        self::ID_SANDBOX11 => 'sandbox11',
        self::ID_SANDBOX12 => 'sandbox12',
        self::ID_SANDBOX13 => 'sandbox13',
        self::ID_SANDBOX14 => 'sandbox14',
        self::ID_DZ4_GMBH => 'dz-4',
        self::ID_WEBER_HOLDING => 'weberholding',
        self::ID_ZEBRA_LERN => 'zebra',
        self::ID_MEINESTADT_LERN => 'meinestadt',
        self::ID_SANDBOX15 => 'sandbox15',
        self::ID_SANDBOX16 => 'sandbox16',
        self::ID_SANDBOX17 => 'sandbox17',
        self::ID_SANDBOX18 => 'sandbox18',
        self::ID_SANDBOX19 => 'sandbox19',
        self::ID_SANDBOX20 => 'sandbox20',
        self::ID_TOACADEMY => 'tundo',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function tagGroups()
    {
        return $this->hasMany(TagGroup::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function profiles()
    {
        return $this->hasMany(AppProfile::class);
    }

    /**
     * These are the apps that this app inherits (course) templates to
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function templateInheritanceChildren()
    {
        return $this->belongsToMany(App::class, 'app_template_inheritances', 'app_id', 'child_id')->withTimestamps();
    }

    /**
     * These are the apps that this app inherits (course) templates from
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function templateInheritanceParents()
    {
        return $this->belongsToMany(App::class, 'app_template_inheritances', 'child_id', 'app_id')->withTimestamps();
    }

    /**
     * This returns all course templates that are inherited from another app
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function inheritedCourseTemplates()
    {
        return $this
            ->belongsToMany(Course::class, 'course_template_inheritances', 'app_id', 'course_id')
            ->withoutGlobalScope('not_template');
    }

    public function getNotificationMails()
    {
        $appProfile = $this->getDefaultAppProfile();
        $mails = $appProfile->getValue('notification_mails');
        if (!$mails) {
            return null;
        }
        $mails = str_replace(',', ';', $mails);
        return array_filter(array_map('trim', explode(';', $mails)));
    }

    /**
     * Checks if this app has temporary accounts.
     *
     * @return bool
     */
    public function hasTmpAccounts()
    {
        $allowedApps = [
            self::ID_WEBDEV_QUIZ,
            self::ID_LINGOMINT,
            self::ID_TALENT_THINKING,
            self::ID_CLARIFY,
            self::ID_DECISIO,
            self::ID_MONEYCOASTER,
        ];
        if (in_array($this->id, $allowedApps)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if this app allows new users to sign up.
     *
     * @return bool
     */
    public function hasSignup()
    {
        if (env('DEBUG_ALLOW_SIGNUP') === true) {
            return true;
        }
        $allowedApps = [
            self::ID_WEBDEV_QUIZ,
            self::ID_DEUTSCHKURS_MEDIZIN,
            self::ID_LINGOMINT,
            self::ID_OPENGRID,
            self::ID_BAYER,
            self::ID_THM,
            self::ID_TALENT_THINKING,
            self::ID_SIGNIA_PRO,
            self::ID_DGQ,
            self::ID_CLARIFY,
            self::ID_VOLKSBANKEN_RAIFFEISENBANKEN,
            self::ID_OLYMPUS,
            self::ID_KEEUNIT_DEMO,
            self::ID_DECISIO,
            self::ID_VILLEROY_BOCH,
            self::ID_JOCHEN_SCHWEIZER,
            self::ID_HNO,
            self::ID_ARAG,
            self::ID_MONEYCOASTER,
            self::ID_WICHTEL_WISSEN,
            self::ID_CURATOR,
            self::ID_TXP_ACADEMY,
            self::ID_STAYATHOME,
            self::ID_GENOAKADEMIE,
            self::ID_GREEN_CARE,
            self::ID_SCHIRRMACHER,
            self::ID_CLIO,
            self::ID_ILEARN,
            self::ID_CARGO_BULL,
        ];

        // New apps don't have to be explicitly listed here anymore, because this can be configured via the app profiles

        return in_array($this->id, $allowedApps);
    }

    public function hasInsecurePasswordChange()
    {
        if($this->id >= self::ID_DD_TEAM) {
            return true;
        }
        return in_array($this->id, [
            self::ID_WUESTENROT,
            self::ID_OPENGRID,
            self::ID_SCHWAEBISCH_HALL,
            self::ID_M2,
            self::ID_KEEUNIT_DEMO,
            self::ID_CURATOR,
            self::ID_NEXUS_KIS,
            self::ID_BAYER,
            self::ID_WIKA,
            self::ID_RAIFFEISEN,
            self::ID_HEIDELBERG,
            self::ID_THM,
            self::ID_APOBANK,
            self::ID_TALENT_THINKING,
            self::ID_NEXUS_INTERN,
            self::ID_DGQ,
            self::ID_SIGNIA_PRO,
            self::ID_IHK,
            self::ID_VOLKSBANKEN_RAIFFEISENBANKEN,
            self::ID_CLARIFY,
            self::ID_OLYMPUS,
            self::ID_TSK,
            self::ID_DMI,
            self::ID_KEEUNIT_HR,
            self::ID_FORD,
            self::ID_ERFSTADT,
            self::ID_DECISIO,
            self::ID_VILLEROY_BOCH,
            self::ID_JOCHEN_SCHWEIZER,
            self::ID_HNO,
            self::ID_ARAG,
            self::ID_MONEYCOASTER,
            self::ID_WICHTEL_WISSEN,
            self::ID_CARGO_BULL,
            self::ID_TXP_ACADEMY,
            self::ID_STAYATHOME,
            self::ID_SUTTER,
            self::ID_FKZ,
            self::ID_YOURFIRM,
            self::ID_GREEN_CARE,
            self::ID_SCHIRRMACHER,
            self::ID_CLIO,
            self::ID_ILEARN,
            self::ID_HASCO,
            self::ID_VKBILDUNG,
            self::ID_KEELEARNING_KURSE,
            self::ID_DD_TEAM,
        ]);
    }

    /**
     * Allows to sign up & log in without using a mail address.
     *
     * @return bool
     */
    public function allowMaillessSignup()
    {
        if(in_array($this->id, [
            self::ID_VOLKSBANKEN_RAIFFEISENBANKEN,
            self::ID_WICHTEL_WISSEN,
            self::ID_STAYATHOME,
            self::ID_GREEN_CARE,
            self::ID_CLIO,
            self::ID_ILEARN,
        ])) {
            return true;
        }
        $appProfile = $this->getDefaultAppProfile();
        if ($appProfile->getValue('signup_show_email_mandatory') !== 'mandatory') {
            return true;
        }
        return false;
    }

    public function uniqueUsernames(): bool
    {
        // usernames must be unique if used for login/signup
        if ($this->allowMaillessSignup() && !$this->getLoginMetaField()) {
            return true;
        }

        return in_array($this->id, [
            self::ID_LINGOMINT,
            self::ID_SIGNIA_PRO,
        ]);
    }

    /**
     * Apps with account activation have their users being inactive when they sign up
     * until they confirm their account via the link in the welcome email.
     *
     * @return bool
     */
    public function needsAccountActivation()
    {
        // No account activation possible with mailless signup
        if ($this->allowMaillessSignup()) {
            return false;
        }

        if($this->id >= self::ID_ORL) {
            return true;
        }

        return in_array($this->id, [
            self::ID_KEEUNIT_DEMO,
            self::ID_SIGNIA_PRO,
            self::ID_THM,
            self::ID_VILLEROY_BOCH,
            self::ID_JOCHEN_SCHWEIZER,
            self::ID_DGQ,
            self::ID_OLYMPUS,
            self::ID_DECISIO,
            self::ID_HNO,
            self::ID_ARAG,
            self::ID_MONEYCOASTER,
            self::ID_WICHTEL_WISSEN,
            self::ID_TXP_ACADEMY,
            self::ID_STAYATHOME,
            self::ID_SUTTER,
            self::ID_FKZ,
            self::ID_GENOAKADEMIE,
            self::ID_SCHIRRMACHER,
            self::ID_CARGO_BULL,
            self::ID_KEELEARNING_KURSE,
            self::ID_DD_TEAM,
            self::ID_BALCONY,
        ]);
    }

    /**
     * Returns the amount of games a user can have with the same user at the same time.
     *
     * @return int
     */
    public function maxConcurrentGames()
    {
        return 5;
    }

    public function hasDeepstream()
    {
        if (\Config::get('services.deepstream.disabled')) {
            return false;
        }

        if($this->id >= self::ID_DD_TEAM) {
            return true;
        }

        return in_array($this->id, [
            self::ID_WUESTENROT,
            self::ID_KEEUNIT_DEMO,
            self::ID_LINGOMINT,
            self::ID_FORD,
            self::ID_ZFZ,
            self::ID_CURATOR,
            self::ID_NEXUS_KIS,
            self::ID_WIKA,
            self::ID_M2,
            self::ID_BAYER,
            self::ID_RAIFFEISEN,
            self::ID_HEIDELBERG,
            self::ID_THM,
            self::ID_APOBANK,
            self::ID_TALENT_THINKING,
            self::ID_NEXUS_INTERN,
            self::ID_SIGNIA_PRO,
            self::ID_GENOAKADEMIE,
            self::ID_DGQ,
            self::ID_IHK,
            self::ID_VOLKSBANKEN_RAIFFEISENBANKEN,
            self::ID_CLARIFY,
            self::ID_OLYMPUS,
            self::ID_TSK,
            self::ID_DMI,
            self::ID_KEEUNIT_HR,
            self::ID_ERFSTADT,
            self::ID_DECISIO,
            self::ID_VILLEROY_BOCH,
            self::ID_YOURFIRM,
            self::ID_HNO,
            self::ID_ARAG,
            self::ID_MONEYCOASTER,
            self::ID_WICHTEL_WISSEN,
            self::ID_CARGO_BULL,
            self::ID_TXP_ACADEMY,
            self::ID_HORMOSAN,
            self::ID_STAYATHOME,
            self::ID_SUTTER,
            self::ID_FKZ,
            self::ID_GREEN_CARE,
            self::ID_SCHIRRMACHER,
            self::ID_CLIO,
            self::ID_ILEARN,
            self::ID_HASCO,
            self::ID_VKBILDUNG,
            self::ID_KEELEARNING_KURSE,
            self::ID_DD_TEAM,
        ]);
    }

    /**
     * App will only allow signup with first and last name given.
     *
     * @return bool
     */
    public function needsNameForSignup()
    {
        return in_array($this->id, [
            self::ID_HNO,
            self::ID_ARAG,
            self::ID_WICHTEL_WISSEN,
            self::ID_TXP_ACADEMY,
            self::ID_GENOAKADEMIE,
            self::ID_GREEN_CARE,
            self::ID_SCHIRRMACHER,
            self::ID_ILEARN,
            self::ID_CARGO_BULL,
        ]);
    }

    /**
     * App will only allow signup with a valid voucher?
     *
     * @return bool
     */
    public function needsVoucherForSignup($email)
    {
        switch ($this->id) {
            case self::ID_OLYMPUS:
                $domains = [
                    'keymed.co.uk',
                    'olympus-europa-holding.co.uk',
                    'olympus-europa.com',
                    'olympus-mea.com',
                    'olympus-oste.com',
                    'olympus-oste.de',
                    'olympus-oste.eu',
                    'olympus-sis.com',
                    'olympus.at',
                    'olympus.be',
                    'olympus.bg',
                    'olympus.ch',
                    'olympus.co.ru',
                    'olympus.co.uk',
                    'olympus.com.ru',
                    'olympus.com.tr',
                    'olympus.cz',
                    'olympus.de',
                    'olympus.dk',
                    'olympus.ee',
                    'olympus.es',
                    'olympus.eu',
                    'olympus.fi',
                    'olympus.fr',
                    'olympus.gr',
                    'olympus.hr',
                    'olympus.hu',
                    'olympus.ie',
                    'olympus.it',
                    'olympus.lt',
                    'olympus.lu',
                    'olympus.lv',
                    'olympus.nl',
                    'olympus.no',
                    'olympus.pt',
                    'olympus.ro',
                    'olympus.rs',
                    'olympus.se',
                    'olympus.si',
                    'olympus.sk',
                    'olympus.ua',
                    'olympus.uk.com',
                    'osfc.cz',
                ];
                break;
            case self::ID_TEACH_ME_PRO:
                $domains = [
                    'power.cloud',
                    'hsag.info',
                    'gates-services.de',
                ];
                break;
            default:
                $domains = [];
        }
        if (count($domains)) {
            foreach ($domains as $domain) {
                if (Str::endsWith($email, '@'.$domain)) {
                    return false;
                }
            }

            return true;
        }

        return in_array($this->id, [
            self::ID_DGQ,
            self::ID_KEEUNIT_DEMO,
            self::ID_JOCHEN_SCHWEIZER,
            self::ID_DECISIO,
            self::ID_WICHTEL_WISSEN,
            self::ID_CURATOR,
            self::ID_TXP_ACADEMY,
            self::ID_VOLKSBANKEN_RAIFFEISENBANKEN,
            self::ID_GENOAKADEMIE,
            self::ID_GREEN_CARE,
            self::ID_SCHIRRMACHER,
            self::ID_ILEARN,
            self::ID_CARGO_BULL,
        ]);
    }

    /**
     * Non current apps still use the old question preview.
     *
     * @return bool
     */
    public function hasNewQuestionPreview()
    {
        if (in_array($this->id, [
            self::ID_WEBDEV_QUIZ,
            self::ID_EASYCREDIT,
            self::ID_DEUTSCHKURS_MEDIZIN,
            self::ID_ZFZ,
            self::ID_WUERTTEMBERGISCHE,
            self::ID_MAINZ,
            self::ID_WOHNDARLEHEN,
            self::ID_FORD,
            self::ID_SCHWAEBISCH_HALL,
            self::ID_LINGOMINT,
            self::ID_GENOAKADEMIE,
            self::ID_OPENGRID,
            self::ID_UNI_WEIMAR,
            self::ID_M2,
            self::ID_BAYER,
            self::ID_CURATOR,
            self::ID_NEXUS_KIS,
            self::ID_RAIFFEISEN,
            self::ID_YOURFIRM,
            self::ID_HEIDELBERG,
            self::ID_THM,
            self::ID_APOBANK,
            self::ID_TALENT_THINKING,
            self::ID_NEXUS_INTERN,
        ])) {
            return false;
        }

        return true;
    }

    public static function getLanguageByIdOld($id)
    {
        switch ($id) {
            case self::ID_BAYER:
            case self::ID_CLIO:
            case self::ID_ORL:
                return 'en';
            case self::ID_WUERTTEMBERGISCHE:
            case self::ID_WOHNDARLEHEN:
            case self::ID_ZFZ:
            case self::ID_M2:
            case self::ID_WUESTENROT:
            case self::ID_SCHWAEBISCH_HALL:
            case self::ID_OPENGRID:
            case self::ID_GENOAKADEMIE:
            case self::ID_UNI_WEIMAR:
            case self::ID_FORD:
            case self::ID_DGQ:
            case self::ID_SCHIRRMACHER:
            case self::ID_EMEURER:
            case self::ID_RAVATI:
            case self::ID_PERBILITY:
            case self::ID_BPMO:
            case self::ID_WMF:
            case self::ID_QVNIA:
            case self::ID_FI_TS:
            case self::ID_TEACH_ME_PRO:
            case self::ID_EITCO:
            case self::ID_ZUDUBI:
            case self::ID_KREIS_LIPPE:
            case self::ID_ELEARNING_VG_LW:
            case self::ID_LSWB_EDUCATION:
            case self::ID_SANDBOX10:
            case self::ID_BABTEC:
                return 'de_formal';
            case self::ID_KEEUNIT_DEMO:
            case self::ID_WEBDEV_QUIZ:
            case self::ID_LINGOMINT:
            case self::ID_MAINZ:
            case self::ID_RAIFFEISEN:
            case self::ID_HEIDELBERG:
            case self::ID_THM:
            case self::ID_APOBANK:
            case self::ID_TALENT_THINKING:
            case self::ID_SIGNIA_PRO:
            case self::ID_IHK:
            case self::ID_VOLKSBANKEN_RAIFFEISENBANKEN:
            case self::ID_CLARIFY:
            case self::ID_OLYMPUS:
            case self::ID_TSK:
            case self::ID_DMI:
            case self::ID_KEEUNIT_HR:
            case self::ID_ERFSTADT:
            case self::ID_DECISIO:
            case self::ID_VILLEROY_BOCH:
            case self::ID_JOCHEN_SCHWEIZER:
            case self::ID_HNO:
            case self::ID_ARAG:
            case self::ID_MONEYCOASTER:
            case self::ID_WICHTEL_WISSEN:
            case self::ID_CURATOR:
            case self::ID_CARGO_BULL:
            case self::ID_TXP_ACADEMY:
            case self::ID_HORMOSAN:
            case self::ID_STAYATHOME:
            case self::ID_SUTTER:
            case self::ID_FKZ:
            case self::ID_GREEN_CARE:
            case self::ID_ILEARN:
            case self::ID_VKBILDUNG:
            case self::ID_KEELEARNING_KURSE:
            case self::ID_DD_TEAM:
            case self::ID_BALCONY:
            case self::ID_EGN:
            case self::ID_WENTZEL_DR:
            case self::ID_REINHOLD:
            case self::ID_KABS:
            case self::ID_OCHAIRSYSTEMS:
            case self::ID_NOVAHEAL:
            case self::ID_AWO_BREMERHAVEN:
            case self::ID_SANDBOX:
            case self::ID_TK:
            case self::ID_VFTC:
            case self::ID_SANDBOX2:
            case self::ID_ECOTEL:
            case self::ID_SANDBOX3:
            case self::ID_ACANDIS:
            case self::ID_ATESTEO:
            case self::ID_TEAMWILLE:
            case self::ID_MUNICH_EMERGENCY_MEDICAL_SERVICES:
            case self::ID_REBEQ:
            case self::ID_DVELOP:
            case self::ID_VZ:
            case self::ID_BOLAB:
            case self::ID_DIV:
            case self::ID_DC:
            case self::ID_DAF:
            case self::ID_SANDBOX5:
            case self::ID_SANDBOX6:
            case self::ID_UCKERMARKER:
            case self::ID_ISLAMKOLLEG:
            case self::ID_LUSINI:
            case self::ID_BLUME2000:
            case self::ID_KEELEARNING_TEMPLATES:
            case self::ID_ITSC:
            case self::ID_LEA:
            case self::ID_HAMBURGER_WASSERWERKE:
            case self::ID_ALLISON:
            case self::ID_FIT_FOOD_BOX:
            case self::ID_SWISSCOM:
            case self::ID_WR_ACADEMY:
            case self::ID_FIT_FOR_TRADE:
            case self::ID_MARKAS:
            case self::ID_DVELOP_AG:
            case self::ID_HAENEL:
            case self::ID_SUPERADMIN_VORLAGEN:
            case self::ID_PLAYGROUND:
            case self::ID_GREEN_CARE_DEMO:
            case self::ID_BOGDOL:
            case self::ID_SMART_UP_NRW:
            case self::ID_TFK_ACADEMY:
            case self::ID_PERSOLOG:
            case self::ID_OREX:
            case self::ID_TEN_PLUS_TWO:
            case self::ID_MIKROBIOM:
            case self::ID_SANDBOX9:
            case self::ID_K2_LEGAL:
            case self::ID_CYPRESS:
            case self::ID_REHAPLUS:
            case self::ID_UNIKLINIK_ROSTOCK:
            case self::ID_OBER:
            case self::ID_GO_CAMPUS:
            case self::ID_GA_LEMPLATTFORM:
            case self::ID_STRIEGA:
            case self::ID_MS_DIRECT_ACADEMY:
            case self::ID_CARBONI:
            case self::ID_BROT_UND_SINNE:
            case self::ID_BOCKHOLT:
            case self::ID_THERAPEUTIKUM_BARSSEL:
            case self::ID_SANDBOX11:
            case self::ID_SANDBOX12:
            case self::ID_SANDBOX13:
            case self::ID_SANDBOX14:
            case self::ID_DZ4_GMBH:
            case self::ID_WEBER_HOLDING:
            case self::ID_ZEBRA_LERN:
            default:
                if(isset(self::LANGUAGES[$id])) {
                    return self::LANGUAGES[$id][0];
                }
                return 'de';
        }
    }

    public static function getLanguageById($id)
    {
        $appSettings = new AppSettings($id);
        return $appSettings->getValue('defaultLanguage');
    }

    public static function getLanguagesByIdOld($id)
    {
        if (array_key_exists($id, self::LANGUAGES)) {
            return self::LANGUAGES[$id];
        } else {
            return [self::getLanguageById($id)];
        }
    }

    public static function getLanguagesById($id)
    {
        $appSettings = new AppSettings($id);
        $languagesSetting = $appSettings->getValue('languages');
        if($languagesSetting) {
           $languages = json_decode($languagesSetting);
        } else {
            $languages = [];
        }
        $languages = [
            $appSettings->getValue('defaultLanguage'),
            ...$languages,
        ];
        return array_values(array_unique($languages));
    }

    public function getLanguage()
    {
        return self::getLanguageById($this->id);
    }

    public function getLanguages()
    {
        return self::getLanguagesById($this->id);
    }

    /**
     * Max failed login attempts at which user gets locked
     * CLONED IN STATS SERVER
     *
     * @return integer
     */
    public function getMaxFailedLoginAttempts(): int
    {
        return 5;
    }

    public function isMailValid($mail)
    {
        switch ($this->id) {
            case self::ID_THM:
                if (! Str::endsWith($mail, '@thm.de') && ! Str::endsWith($mail, '.thm.de')) {
                    return __('errors.mail_cant_be_used');
                }
                break;
            case self::ID_OPENGRID:
                if (! Str::endsWith($mail, '@open-grid-europe.com')) {
                    return __('errors.mail_cant_be_used');
                }
                break;
            case self::ID_BAYER:
                if (! Str::endsWith($mail, '@bayer.com')) {
                    return __('errors.mail_cant_be_used');
                }
                if (Str::endsWith($mail, '.ext@bayer.com')) {
                    return __('bayer.errors.only_internal_workers_yet');
                }
                break;
            case self::ID_TSK:
                if (!Str::endsWith($mail, ['@taunus-sparkasse.de'])) {
                    return __('errors.mail_cant_be_used');
                }
                break;
            case self::ID_TK:
                $startDate = Carbon::createFromFormat('Y-m-d H:i:s','2021-10-18 00:00:00');
                $endDate = Carbon::createFromFormat('Y-m-d H:i:s','2021-11-30 23:59:59');
                $hasOpenAccess = Carbon::now()->between($startDate,$endDate);

                if (!$hasOpenAccess && !Str::endsWith($mail, ['@tk.de'])) {
                    return __('errors.mail_cant_be_used');
                }
                break;
            case self::ID_ARAG:
                if (! Str::endsWith($mail, [
                    '@arag.de',
                    '@arag-partner.de',
                ])) {
                    return __('errors.mail_cant_be_used');
                }
                break;
            case self::ID_FI_TS:
                if (! Str::endsWith($mail, [
                    '@f-i-ts.de',
                    '@mailbox.org',
                ])) {
                    return __('errors.mail_cant_be_used');
                }
                break;
            default:
                break;
        }

        return true;
    }

    public function getUserMetaDataFields(bool $showPersonalData = false): array
    {
        $metaDataFields = collect($this->getAllUserMetaDataFields());
        if (!$showPersonalData) {
            $metaDataFields = $metaDataFields->filter(function ($metaDataField) {
                return !$metaDataField['personal_data'];
            });
        }
        return $metaDataFields->toArray();
    }

    /**
     * Checks if the app has metadata that may contain personal user data
     *
     * @return boolean
     */
    public function hasPersonalMetaData(): bool
    {
        return collect($this->getAllUserMetaDataFields())
            ->contains('personal_data', true);
    }

    /**
     * Get the custom user metadata fields for the app.
     *
     * label: user-facing title of field
     * description: user-facing description of field content
     * import: can be batch imported
     * compare: is available for comparison in user import
     * signup: is shown and has to be filled at signup
     * signup_optional: does not have to be filled at signup (false by default)
     * type: string, date (YYYY-MM-DD)
     * personal_data: boolean, hides this field based on admin rights
     *
     * @param boolean $showPrivateData
     *
     * @return array
     */
    public function getAllUserMetaDataFields()
    {
        switch ($this->id) {
            case self::ID_SCHWAEBISCH_HALL:
                return [
                    'voNr' => [
                        'label' => 'Vo Nr.',
                        'type' => 'string',
                        'import' => true,
                        'compare' => true,
                        'signup' => true,
                        'personal_data' => false,
                        'unique' => false,
                    ],
                ];
            case self::ID_FORD:
                return [
                    'pin' => [
                        'label' => 'PIN',
                        'type' => 'string',
                        'import' => true,
                        'compare' => true,
                        'signup' => true,
                        'personal_data' => true,
                        'unique' => false,
                    ],
                    'lastname' => [
                        'label' => 'Nachname',
                        'type' => 'string',
                        'import' => true,
                        'compare' => true,
                        'signup' => true,
                        'personal_data' => true,
                        'unique' => false,
                    ],
                ];
            case self::ID_SIGNIA_PRO:
                return [
                    'salesforce-id' => [
                        'label' => 'Salesforce-ID',
                        'type' => 'string',
                        'import' => true,
                        'compare' => true,
                        'signup' => false,
                        'personal_data' => true,
                        'unique' => false,
                    ],
                    'vendor' => [
                        'label' => 'Händler-Kundennummer',
                        'type' => 'string',
                        'import' => true,
                        'compare' => true,
                        'signup' => true,
                        'personal_data' => false,
                        'unique' => false,
                    ],
                    'postcode' => [
                        'label' => 'Postleitzahl',
                        'type' => 'string',
                        'import' => true,
                        'compare' => true,
                        'signup' => false,
                        'personal_data' => false,
                        'unique' => false,
                    ],
                ];
            case self::ID_VOLKSBANKEN_RAIFFEISENBANKEN:
                return [
                    'schoolname' => [
                        'label'   => 'Name deiner Schule',
                        'type'    => 'string',
                        'import'  => false,
                        'compare' => false,
                        'signup'  => true,
                        'personal_data' => false,
                        'unique' => false,
                    ],
                    'classname' => [
                        'label'         => 'In welche Klasse gehst du (z.B. 8 a; 8.1)?',
                        'type'          => 'string',
                        'import'        => false,
                        'compare'       => false,
                        'signup'        => true,
                        'personal_data' => false,
                        'unique'        => false,
                    ],
                ];
            case self::ID_GREEN_CARE:
                return [
                    'birthday' => [
                        'label'         => __('auth.meta.birthday'),
                        'type'          => 'date',
                        'import'        => true,
                        'compare'       => false,
                        'signup'        => false,
                        'personal_data' => true,
                        'unique'        => false,
                    ],
                    'company' => [
                        'label'         => __('auth.meta.company_name'),
                        'type'          => 'string',
                        'import'        => true,
                        'compare'       => false,
                        'signup'        => true,
                        'personal_data' => false,
                        'unique'        => false,
                    ],
                ];
            case self::ID_BPMO:
                return [
                    'company' => [
                        'label'           => __('auth.meta.bpmo.company_name'),
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => true,
                        'signup'          => true,
                        'signup_optional' => false,
                        'personal_data'   => false,
                        'unique'          => false,
                    ],
                    'phone' => [
                        'label'           => __('auth.meta.bpmo.phone_number'),
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => true,
                        'signup'          => true,
                        'signup_optional' => false,
                        'personal_data'   => true,
                        'unique'          => false,
                    ],
                ];
            case self::ID_ILEARN:
                return [
                    'rhenusid' => [
                        'label'           => 'RhenusID',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => true,
                        'signup'          => false,
                        'signup_optional' => false,
                        'personal_data'   => true,
                        'unique'          => false,
                    ],
                ];
            case self::ID_ALLISON:
                return [
                    'vendorname' => [
                        'label'           => 'Händlername inkl. Ort',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => false,
                        'signup'          => true,
                        'signup_optional' => false,
                        'unique'          => false,
                    ],
                ];
            case self::ID_BLUME2000:
                return [
                    'login' => [
                        'label'           => 'Benutzerlogin',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => true,
                        'personal_data'   => true,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => true,
                    ],
                    'personalnumber' => [
                        'label'           => 'Personalnummer',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => true,
                        'personal_data'   => true,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => true,
                    ],
                ];
            case self::ID_MARKAS:
                return [
                    'oracle_number' => [
                        'label'           => 'Personennummer',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => true,
                        'personal_data'   => false,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => true,
                    ],
                    'phone_number' => [
                        'label'           => 'Telefonnummer',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => true,
                        'signup_optional' => true,
                        'unique'          => false,
                    ],
                    'entry_date' => [
                        'label'           => 'Eintrittsdatum',
                        'type'            => 'date',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => false,
                    ],
                    'exit_date' => [
                        'label'           => 'Austrittsdatum',
                        'type'            => 'date',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => false,
                    ],
                    'cost_center' => [
                        'label'           => 'Kostenstelle',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => false,
                    ],
                    'manager' => [
                        'label'           => 'Manager',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => false,
                    ],
                    'occupation' => [
                        'label'           => 'Taetigkeit',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => false,
                    ],
                ];
            case self::ID_TEACH_ME_PRO:
                return [
                    'company' => [
                        'label'           => 'Unternehmen',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => true,
                        'signup_optional' => false,
                        'unique'          => false,
                    ],
                ];
            case self::ID_MIKROBIOM:
                return [
                    'miscellaneous' => [
                        'label'           => 'Nur bei Auswahl "Sonstiges": Wie ist deine Berufsbezeichnung',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => true,
                        'signup_optional' => true,
                        'unique'          => false,
                    ],
                ];
            case self::ID_VZ:
                return [
                    'vzid' => [
                        'label'           => 'VZ-ID',
                        'type'            => 'string',
                        'import'          => false,
                        'compare'         => true,
                        'personal_data'   => true,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => true,
                    ],
                ];
            case self::ID_GO_CAMPUS:
                return [
                    'guid' => [
                        'label'           => 'GUID',
                        'type'            => 'string',
                        'import'          => false,
                        'compare'         => true,
                        'personal_data'   => false,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => true,
                    ],
                ];
            case self::ID_BABTEC:
                return [
                    'company' => [
                        'label'           => 'Firma',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => false,
                    ],
                ];
            case self::ID_REHAPLUS:
                return [
                    'location' => [
                        'label'           => 'Standort',
                        'type'            => 'string',
                        'import'          => true,
                        'compare'         => false,
                        'personal_data'   => false,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => false,
                    ],
                ];
            case self::ID_MS_DIRECT_ACADEMY:
                return [
                    'ms_id' => [
                        'label'           => 'MS-Direct ID',
                        'type'            => 'string',
                        'import'          => false,
                        'compare'         => true,
                        'personal_data'   => true,
                        'signup'          => false,
                        'signup_optional' => false,
                        'unique'          => true,
                    ],
                ];
            default:
                return [];
        }
    }

    public function getSignupMetaFields()
    {
        return collect($this->getUserMetaDataFields(true))
            ->where('signup', true)
            ->map(function ($metaField) {
                return collect($metaField)
                    ->only([
                        'label',
                        'type',
                        'description',
                        'signup_optional',
                    ]);
            })
            ->toArray();
    }

    /**
     * Returns the string of the meta field used for logging in,
     * or null.
     *
     * @return string|null
     */
    public function getLoginMetaField(): ?string
    {
        switch ($this->id) {
            case self::ID_BLUME2000:
                return 'login';
            case self::ID_MARKAS:
                return 'oracle_number';
        }
        return null;
    }

    /**
     * Returns an array of meta field keys which must be unique.
     *
     * @return array
     */
    public function getUniqueMetaFields(): array
    {
        $loginMetaField = $this->getLoginMetaField();
        return collect($this->getAllUserMetaDataFields())
            ->filter(function ($metaField, $key) use ($loginMetaField) {
                if ($loginMetaField && $loginMetaField == $key) {
                    return true;
                }
                return isset($metaField['unique']) && $metaField['unique'];
            })
            ->keys()
            ->toArray();
    }

    /**
     * Hardcoded if the app uses specific avatars.
     * @return bool
     */
    public function useSpecificAvatars()
    {
        return in_array($this->id, [
            self::ID_OLYMPUS,
            self::ID_DECISIO,
        ]);
    }

    /**
     * Hardcoded if the app uses specific bots.
     * @return bool
     */
    public function useSpecificBots()
    {
        return in_array($this->id, [
            self::ID_DECISIO,
        ]);
    }

    /**
     * App uses simpler Power Learning.
     * @return bool
     */
    public function usePowerLearning()
    {
        if($this->id >= self::ID_BALCONY) {
            return true;
        }
        return in_array($this->id, [
            self::ID_KEEUNIT_DEMO,
            self::ID_APOBANK,
            self::ID_TSK,
            self::ID_DMI,
            self::ID_KEEUNIT_HR,
            self::ID_ERFSTADT,
            self::ID_RAIFFEISEN,
            self::ID_SIGNIA_PRO,
            self::ID_GENOAKADEMIE,
            self::ID_DECISIO,
            self::ID_VILLEROY_BOCH,
            self::ID_JOCHEN_SCHWEIZER,
            self::ID_WIKA,
            self::ID_HNO,
            self::ID_M2,
            self::ID_ARAG,
            self::ID_MONEYCOASTER,
            self::ID_WICHTEL_WISSEN,
            self::ID_CURATOR,
            self::ID_CARGO_BULL,
            self::ID_HORMOSAN,
            self::ID_STAYATHOME,
            self::ID_SUTTER,
            self::ID_YOURFIRM,
            self::ID_NEXUS_INTERN,
            self::ID_SCHIRRMACHER,
            self::ID_CLIO,
            self::ID_ILEARN,
            self::ID_HASCO,
            self::ID_VKBILDUNG,
            self::ID_KEELEARNING_KURSE,
            self::ID_DD_TEAM,
        ]);
    }

    /**
     * Returns a custom email address from which all mails should be sent.
     *
     * @return bool|string
     */
    public function customEmailSender()
    {
        switch ($this->id) {
            case self::ID_SUTTER:
                return 'elearning@sutter.ruhr';
            default:
                return false;
        }
    }

    /**
     * Disables the signup in live environments based on app ID
     *
     * @return bool
     */
    public function demoSignupDisabled(): bool
    {
        if (!live()) {
            return false;
        }
        return $this->id == self::ID_SANDBOX3;
    }

    /**
     * Return the default app profile.
     *
     * @return AppProfile
     */
    public function getDefaultAppProfile()
    {
        $defaultProfile = $this->getCachedDefaultAppProfile();
        if($defaultProfile) {
            return $defaultProfile;
        }

        return $this->profiles
            ->where('is_default', true)
            ->first();
    }

    private function getCachedDefaultAppProfile() {
        if (! isset(self::$cacheDefaultAppProfile[$this->id])) {
            self::$cacheDefaultAppProfile[$this->id] = $this->profiles
                ->where('is_default', true)
                ->first();
        }

        return self::$cacheDefaultAppProfile[$this->id];
    }

    public function hasXAPI()
    {
        return !!$this->xapi_token;
    }

    public function getAppNameAttribute()
    {
        return $this->getDefaultAppProfile()->getValue('app_name');
    }

    public function getLogoUrlAttribute()
    {
        return $this->getDefaultAppProfile()->getValue('app_icon') ?: '/img/logos/' . $this->id . '.png';
    }

    static public function clearStaticCache()
    {
        self::$cacheDefaultAppProfile = [];
    }

    public function containsTagsInReport():bool {
        if($this->id === self::ID_GREEN_CARE) {
            return false;
        }
        return true;
    }
}
