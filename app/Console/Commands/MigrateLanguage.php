<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\FrontendTranslation;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class MigrateLanguage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:language {appId} {oldlang} {newlang}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate application models language to new one, e.g. from de to de_formal';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $appId = (int) $this->argument('appId');
        $newlang = $this->argument('newlang');
        $oldlang = $this->argument('oldlang');

        // make sure the app ID is valide
        App::findOrFail($appId)->exists();

        $langUsed = self::languagesUsedByApp($appId);
        if ($langUsed->contains($newlang)) {
            $appName = App::SLUGS[$appId];
            $this->info("App '$appName' (ID $appId) already uses '$newlang'.");
            if(!$this->confirm('Are you sure you want to continue?')) {
                return;
            }
        }

        DB::beginTransaction();
        try {
            $update_at = Carbon::now();

            self::migrateAdvertisements($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate advertisements');
            self::migrateAppointments($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate appointments');
            self::migrateCategories($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate categories');
            self::migrateCategoryGroups($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate category groups');
            self::migrateCertificateTemplates($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate certificate templates');
            self::migrateContentCategories($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate content categories');
            self::migrateCourses($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate courses');
            self::migrateFrontendTranslations($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate frontend translations');
            self::migrateForms($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate forms');
            self::migrateKeywords($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate keywords');
            self::migrateMailTemplates($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate mail templates');
            self::migrateNews($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate news');
            self::migratePages($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate pages');
            self::migrateQuestionAnswers($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate question answers');
            self::migrateQuestions($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate questions');
            self::migrateTests($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate tests');
            self::migrateUsers($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate users');
            self::migrateMediaLibrary($appId, $oldlang, $newlang, $update_at);
            $this->info('Done - migrate medialibrary');

            $this->info('Done');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public static function migrateAdvertisements($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('advertisement_translations')
            ->join('advertisements', 'advertisements.id', '=', 'advertisement_translations.advertisement_id')
            ->where('advertisements.app_id', $appId)
            ->where('advertisement_translations.language', $oldlang)
            ->update([
                'advertisement_translations.language'   => $newlang,
                'advertisement_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateAppointments($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('appointment_translations')
            ->join('appointments', 'appointments.id', '=', 'appointment_translations.appointment_id')
            ->where('appointments.app_id', $appId)
            ->where('appointment_translations.language', $oldlang)
            ->update([
                'appointment_translations.language'   => $newlang,
                'appointment_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateCategories($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('category_translations')
            ->join('categories', 'categories.id', '=', 'category_translations.category_id')
            ->where('categories.app_id', $appId)
            ->where('category_translations.language', $oldlang)
            ->update([
                'category_translations.language'   => $newlang,
                'category_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateCategoryGroups($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('categorygroup_translations')
            ->join('categorygroups', 'categorygroups.id', '=', 'categorygroup_translations.categorygroup_id')
            ->where('categorygroups.app_id', $appId)
            ->where('categorygroup_translations.language', $oldlang)
            ->update([
                'categorygroup_translations.language'   => $newlang,
                'categorygroup_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateCertificateTemplates($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('certificate_template_translations')
            ->join('certificate_templates', 'certificate_templates.id', '=', 'certificate_template_translations.certificate_template_id')
            ->join('tests', 'certificate_templates.test_id', '=', 'tests.id')
            ->where('tests.app_id', $appId)
            ->where('certificate_template_translations.language', $oldlang)
            ->update([
                'certificate_template_translations.language'   => $newlang,
                'certificate_template_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateContentCategories($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('content_category_translations')
            ->join('content_categories', 'content_categories.id', '=', 'content_category_translations.content_category_id')
            ->where('content_categories.app_id', $appId)
            ->where('content_category_translations.language', $oldlang)
            ->update([
                'content_category_translations.language'   => $newlang,
                'content_category_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateCourses($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('course_chapter_translations')
            ->join('course_chapters', 'course_chapters.id', '=', 'course_chapter_translations.course_chapter_id')
            ->join('courses', 'course_chapters.course_id', '=', 'courses.id')
            ->where('courses.app_id', $appId)
            ->where('course_chapter_translations.language', $oldlang)
            ->update([
                'course_chapter_translations.language'   => $newlang,
                'course_chapter_translations.updated_at' => $updated_at,
            ]);

        DB::table('course_content_translations')
            ->join('course_contents', 'course_contents.id', '=', 'course_content_translations.course_content_id')
            ->join('course_chapters', 'course_chapters.id', '=', 'course_contents.course_chapter_id')
            ->join('courses', 'course_chapters.course_id', '=', 'courses.id')
            ->where('courses.app_id', $appId)
            ->where('course_content_translations.language', $oldlang)
            ->update([
                'course_content_translations.language'   => $newlang,
                'course_content_translations.updated_at' => $updated_at,
            ]);

        DB::table('course_translations')
            ->join('courses', 'courses.id', '=', 'course_translations.course_id')
            ->where('courses.app_id', $appId)
            ->where('course_translations.language', $oldlang)
            ->update([
                'course_translations.language'   => $newlang,
                'course_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateForms($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('form_field_translations')
            ->join('form_fields', 'form_fields.id', '=', 'form_field_translations.form_field_id')
            ->join('forms', 'form_fields.form_id', '=', 'forms.id')
            ->where('forms.app_id', $appId)
            ->where('form_field_translations.language', $oldlang)
            ->update([
                'form_field_translations.language'   => $newlang,
                'form_field_translations.updated_at' => $updated_at,
            ]);

        DB::table('form_translations')
            ->join('forms', 'forms.id', '=', 'form_translations.form_id')
            ->where('forms.app_id', $appId)
            ->where('form_translations.language', $oldlang)
            ->update([
                'form_translations.language'   => $newlang,
                'form_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateFrontendTranslations($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('frontend_translations')
            ->join('app_profiles', 'app_profiles.id', '=', 'frontend_translations.app_profile_id')
            ->where('app_profiles.app_id', $appId)
            ->where('frontend_translations.language', $oldlang)
            ->update([
                'frontend_translations.language'   => $newlang,
                'frontend_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateKeywords($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('keyword_translations')
            ->join('keywords', 'keywords.id', '=', 'keyword_translations.keyword_id')
            ->where('keywords.app_id', $appId)
            ->where('keyword_translations.language', $oldlang)
            ->update([
                'keyword_translations.language'   => $newlang,
                'keyword_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateMailTemplates($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('mail_template_translations')
            ->join('mail_templates', 'mail_templates.id', '=', 'mail_template_translations.mail_template_id')
            ->where('mail_templates.app_id', $appId)
            ->where('mail_template_translations.language', $oldlang)
            ->update([
                'mail_template_translations.language'   => $newlang,
                'mail_template_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateMediaLibrary($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('learning_material_folder_translations')
            ->join('learning_material_folders', 'learning_material_folder_translations.learning_material_folder_id', '=', 'learning_material_folders.id')
            ->where('learning_material_folders.app_id', $appId)
            ->where('learning_material_folder_translations.language', $oldlang)
            ->update([
                'learning_material_folder_translations.language'   => $newlang,
                'learning_material_folder_translations.updated_at' => $updated_at,
            ]);

        DB::table('learning_material_translations')
            ->join('learning_materials', 'learning_materials.id', '=', 'learning_material_translations.learning_material_id')
            ->join('learning_material_folders', 'learning_materials.learning_material_folder_id', '=', 'learning_material_folders.id')
            ->where('learning_material_folders.app_id', $appId)
            ->where('learning_material_translations.language', $oldlang)
            ->update([
                'learning_material_translations.language'   => $newlang,
                'learning_material_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateNews($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('news_translations')
            ->join('news', 'news.id', '=', 'news_translations.news_id')
            ->where('news.app_id', $appId)
            ->where('news_translations.language', $oldlang)
            ->update([
                'news_translations.language'   => $newlang,
                'news_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migratePages($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('page_translations')
            ->join('pages', 'pages.id', '=', 'page_translations.page_id')
            ->where('pages.app_id', $appId)
            ->where('page_translations.language', $oldlang)
            ->update([
                'page_translations.language'   => $newlang,
                'page_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateQuestionAnswers($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('question_answer_translations')
            ->join('question_answers', 'question_answers.id', '=', 'question_answer_translations.question_answer_id')
            ->join('questions', 'questions.id', '=', 'question_answers.question_id')
            ->where('questions.app_id', $appId)
            ->where('question_answer_translations.language', $oldlang)
            ->update([
                'question_answer_translations.language'   => $newlang,
                'question_answer_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateQuestions($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('question_translations')
            ->join('questions', 'questions.id', '=', 'question_translations.question_id')
            ->where('questions.app_id', $appId)
            ->where('question_translations.language', $oldlang)
            ->update([
                'question_translations.language'   => $newlang,
                'question_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateTests($appId, $oldlang, $newlang, $updated_at)
    {
        DB::table('test_translations')
            ->join('tests', 'tests.id', '=', 'test_translations.test_id')
            ->where('tests.app_id', $appId)
            ->where('test_translations.language', $oldlang)
            ->update([
                'test_translations.language'   => $newlang,
                'test_translations.updated_at' => $updated_at,
            ]);
    }

    public static function migrateUsers($appId, $oldlang, $newlang, $updated_at)
    {
        User::whereAppId($appId)
            ->whereLanguage($oldlang)
            ->update([
                'language'   => $newlang,
                'updated_at' => $updated_at,
            ]);
    }

    public static function languagesUsedByApp($appId)
    {
        $advertisementLangs = DB::table('advertisement_translations')
            ->distinct()
            ->join('advertisements', 'advertisements.id', '=', 'advertisement_translations.advertisement_id')
            ->where('advertisements.app_id', $appId)
            ->whereNotNull('advertisement_translations.language')
            ->pluck('advertisement_translations.language');

        $appointmentLangs = DB::table('appointment_translations')
            ->distinct()
            ->join('appointments', 'appointments.id', '=', 'appointment_translations.appointment_id')
            ->where('appointments.app_id', $appId)
            ->whereNotNull('appointment_translations.language')
            ->pluck('appointment_translations.language');

        $categoryLangs = DB::table('category_translations')
            ->distinct()
            ->join('categories', 'categories.id', '=', 'category_translations.category_id')
            ->where('categories.app_id', $appId)
            ->whereNotNull('category_translations.language')
            ->pluck('category_translations.language');

        $categoryGroupLangs = DB::table('categorygroup_translations')
            ->distinct()
            ->join('categorygroups', 'categorygroups.id', '=', 'categorygroup_translations.categorygroup_id')
            ->where('categorygroups.app_id', $appId)
            ->whereNotNull('categorygroup_translations.language')
            ->pluck('categorygroup_translations.language');

        $certificateTemplateLangs = DB::table('certificate_template_translations')
            ->distinct()
            ->join('certificate_templates', 'certificate_templates.id', '=', 'certificate_template_translations.certificate_template_id')
            ->join('tests', 'certificate_templates.test_id', '=', 'tests.id')
            ->where('tests.app_id', $appId)
            ->whereNotNull('certificate_template_translations.language')
            ->pluck('certificate_template_translations.language');

        $contentCategoryLangs = DB::table('content_category_translations')
            ->distinct()
            ->join('content_categories', 'content_categories.id', '=', 'content_category_translations.content_category_id')
            ->where('content_categories.app_id', $appId)
            ->whereNotNull('content_category_translations.language')
            ->pluck('content_category_translations.language');

        $courseChapterLangs = DB::table('course_chapter_translations')
            ->distinct()
            ->join('course_chapters', 'course_chapters.id', '=', 'course_chapter_translations.course_chapter_id')
            ->join('courses', 'course_chapters.course_id', '=', 'courses.id')
            ->where('courses.app_id', $appId)
            ->whereNotNull('course_chapter_translations.language')
            ->pluck('course_chapter_translations.language');

        $courseContentLangs = DB::table('course_content_translations')
            ->distinct()
            ->join('course_contents', 'course_contents.id', '=', 'course_content_translations.course_content_id')
            ->join('course_chapters', 'course_chapters.id', '=', 'course_contents.course_chapter_id')
            ->join('courses', 'course_chapters.course_id', '=', 'courses.id')
            ->where('courses.app_id', $appId)
            ->whereNotNull('course_content_translations.language')
            ->pluck('course_content_translations.language');

        $courseLangs = DB::table('course_translations')
            ->distinct()
            ->join('courses', 'courses.id', '=', 'course_translations.course_id')
            ->where('courses.app_id', $appId)
            ->whereNotNull('course_translations.language')
            ->pluck('course_translations.language');

        $formFieldLangs = DB::table('form_field_translations')
            ->distinct()
            ->join('form_fields', 'form_fields.id', '=', 'form_field_translations.form_field_id')
            ->join('forms', 'form_fields.form_id', '=', 'forms.id')
            ->where('forms.app_id', $appId)
            ->whereNotNull('form_field_translations.language')
            ->pluck('form_field_translations.language');

        $formLangs = DB::table('form_translations')
            ->distinct()
            ->join('forms', 'forms.id', '=', 'form_translations.form_id')
            ->where('forms.app_id', $appId)
            ->whereNotNull('form_translations.language')
            ->pluck('form_translations.language');

        $frontendTranslationLangs = DB::table('frontend_translations')
            ->distinct()
            ->join('app_profiles', 'app_profiles.id', '=', 'frontend_translations.app_profile_id')
            ->where('app_profiles.app_id', $appId)
            ->whereNotNull('frontend_translations.language')
            ->pluck('frontend_translations.language');

        $keywordLangs = DB::table('keyword_translations')
            ->distinct()
            ->join('keywords', 'keywords.id', '=', 'keyword_translations.keyword_id')
            ->where('keywords.app_id', $appId)
            ->whereNotNull('keyword_translations.language')
            ->pluck('keyword_translations.language');

        $learningMaterialFolderLangs = DB::table('learning_material_folder_translations')
            ->distinct()
            ->join('learning_material_folders', 'learning_material_folders.id', '=', 'learning_material_folder_translations.learning_material_folder_id')
            ->where('learning_material_folders.app_id', $appId)
            ->whereNotNull('learning_material_folder_translations.language')
            ->pluck('learning_material_folder_translations.language');

        $learningMaterialLangs = DB::table('learning_material_translations')
            ->distinct()
            ->join('learning_materials', 'learning_materials.id', '=', 'learning_material_translations.learning_material_id')
            ->join('learning_material_folders', 'learning_materials.learning_material_folder_id', '=', 'learning_material_folders.id')
            ->where('learning_material_folders.app_id', $appId)
            ->whereNotNull('learning_material_translations.language')
            ->pluck('learning_material_translations.language');

        $mailTemplateLangs = DB::table('mail_template_translations')
            ->distinct()
            ->join('mail_templates', 'mail_templates.id', '=', 'mail_template_translations.mail_template_id')
            ->where('mail_templates.app_id', $appId)
            ->whereNotNull('mail_template_translations.language')
            ->pluck('mail_template_translations.language');

        $newsLangs = DB::table('news_translations')
            ->distinct()
            ->join('news', 'news.id', '=', 'news_translations.news_id')
            ->where('news.app_id', $appId)
            ->whereNotNull('news_translations.language')
            ->pluck('news_translations.language');

        $pageLangs = DB::table('page_translations')
            ->distinct()
            ->join('pages', 'pages.id', '=', 'page_translations.page_id')
            ->where('pages.app_id', $appId)
            ->whereNotNull('page_translations.language')
            ->pluck('page_translations.language');

        $questionAnswerLangs = DB::table('question_answer_translations')
            ->distinct()
            ->join('question_answers', 'question_answers.id', '=', 'question_answer_translations.question_answer_id')
            ->join('questions', 'questions.id', '=', 'question_answers.question_id')
            ->where('questions.app_id', $appId)
            ->whereNotNull('question_answer_translations.language')
            ->pluck('question_answer_translations.language');

        $questionLangs = DB::table('question_translations')
            ->distinct()
            ->join('questions', 'questions.id', '=', 'question_translations.question_id')
            ->where('questions.app_id', $appId)
            ->whereNotNull('question_translations.language')
            ->pluck('question_translations.language');

        $testLangs = DB::table('test_translations')
            ->distinct()
            ->join('tests', 'tests.id', '=', 'test_translations.test_id')
            ->where('tests.app_id', $appId)
            ->whereNotNull('test_translations.language')
            ->pluck('test_translations.language');

        $userLangs = DB::table('users')
            ->distinct()
            ->whereAppId($appId)
            ->whereNotNull('language')
            ->pluck('language');

        return collect([
            $advertisementLangs,
            $appointmentLangs,
            $categoryLangs,
            $categoryGroupLangs,
            $certificateTemplateLangs,
            $contentCategoryLangs,
            $courseChapterLangs,
            $courseContentLangs,
            $courseLangs,
            $formFieldLangs,
            $formLangs,
            $frontendTranslationLangs,
            $keywordLangs,
            $learningMaterialFolderLangs,
            $learningMaterialLangs,
            $mailTemplateLangs,
            $newsLangs,
            $pageLangs,
            $questionAnswerLangs,
            $questionLangs,
            $testLangs,
            $userLangs,
        ])
        ->flatten()
        ->unique()
        ->values();
    }
}
