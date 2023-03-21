<?php

namespace App\Console\Commands;

use App\Models\AccessLog;
use App\Models\Advertisements\Advertisement;
use App\Models\Advertisements\AdvertisementPosition;
use App\Models\Advertisements\AdvertisementTranslation;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\Appointments\Appointment;
use App\Models\Appointments\AppointmentTranslation;
use App\Models\AppProfile;
use App\Models\AppProfileHomeComponent;
use App\Models\AppProfileSetting;
use App\Models\AppRating;
use App\Models\AppSetting;
use App\Models\AuthToken;
use App\Models\AzureVideo;
use App\Models\Category;
use App\Models\Categorygroup;
use App\Models\CategorygroupTranslation;
use App\Models\CategoryHider;
use App\Models\CategoryTranslation;
use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateTranslation;
use App\Models\CloneRecord;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReport;
use App\Models\Competition;
use App\Models\ContentCategories\ContentCategory;
use App\Models\ContentCategories\ContentCategoryRelation;
use App\Models\ContentCategories\ContentCategoryTranslation;
use App\Models\Courses\Course;
use App\Models\Courses\CourseAccessRequest;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseChapterTranslation;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttachment;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseContentAttemptAttachment;
use App\Models\Courses\CourseContentTranslation;
use App\Models\Courses\CourseParticipation;
use App\Models\Courses\CourseTranslation;
use App\Models\DirectMessage;
use App\Models\EventHistory;
use App\Models\FcmToken;
use App\Models\FrontendTranslation;
use App\Models\Forms\Form;
use App\Models\Forms\FormAnswer;
use App\Models\Forms\FormAnswerField;
use App\Models\Forms\FormField;
use App\Models\Forms\FormFieldTranslation;
use App\Models\Forms\FormTranslation;
use App\Models\Game;
use App\Models\GamePoint;
use App\Models\GameQuestion;
use App\Models\GameQuestionAnswer;
use App\Models\GameRound;
use App\Models\Import;
use App\Models\IndexCard;
use App\Models\Keywords\Keyword;
use App\Models\Keywords\KeywordTranslation;
use App\Models\LearnBoxCard;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\LearningMaterialFolderTranslation;
use App\Models\LearningMaterialTranslation;
use App\Models\Like;
use App\Models\UserMetafield;
use App\Models\UserNotificationSetting;
use App\Models\MailTemplate;
use App\Models\MailTemplateTranslation;
use App\Models\News;
use App\Models\NewsTranslation;
use App\Models\OpenIdToken;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\PrivacyNoteConfirmation;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionAnswerTranslation;
use App\Models\QuestionAttachment;
use App\Models\QuestionDifficulty;
use App\Models\QuestionTranslation;
use App\Models\QuizTeam;
use App\Models\Reminder;
use App\Models\ReminderMetadata;
use App\Models\Reporting;
use App\Models\SuggestedQuestion;
use App\Models\SuggestedQuestionAnswer;
use App\Models\Tag;
use App\Models\TagGroup;
use App\Models\Test;
use App\Models\TestCategory;
use App\Models\TestQuestion;
use App\Models\TestSubmission;
use App\Models\TestSubmissionAnswer;
use App\Models\TestTranslation;
use App\Models\TrainingAnswer;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\UserRole;
use App\Models\UserRoleRight;
use App\Models\Viewcount;
use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Models\Webinar;
use App\Models\WebinarAdditionalUser;
use App\Models\WebinarParticipant;
use App\Services\MorphTypes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune {appid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Completely removes an app from the database. Dangerous!';

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
        $appId = $this->argument('appid');
        $app = App::find($appId);
        if (! $app) {
            $this->error('Could not find app with id #'.$appId);
            if (! $this->confirm('Do you still want to try deleting everything referencing app id #'.$appId.'?')) {
                return;
            }
        } else {
            if (! $this->confirm('Is "'.$app->name.'" the app you want to delete?')) {
                return;
            }
        }

        $lastGame = Game::ofApp($appId)->orderBy('created_at', 'desc')->first();
        if (! $lastGame) {
            $this->line('This app does not have any games. Seems you\'re in the clear!');
        } else {
            $this->line('The last game was started at '.$lastGame->created_at);
        }

        if (! $this->confirm('Are you sure about deleting this app?')) {
            return;
        }

        if ($this->confirm('Did you forget to make a backup?')) {
            $this->info('Better go make a backup first :))');

            return;
        }

        $this->line('Deleting app template inheritances');
        // Table: app_template_inheritances for where the app_id is this app's id
        $app->templateInheritanceChildren()->detach();
        // Table: app_template_inheritances for where the child_id is this app's id
        $app->templateInheritanceParents()->detach();

        $this->line('Deleting Analytics Events…');
        // Table: analytics_event_foreign_tag
        DB::table('analytics_event_foreign_tag')
            ->join('analytics_events', 'analytics_events.id', 'analytics_event_foreign_tag.analytics_event_id')
            ->where('analytics_events.app_id', $appId)
            ->delete();
        // Table: analytics_event_tag
        DB::table('analytics_event_tag')
            ->join('analytics_events', 'analytics_events.id', 'analytics_event_tag.analytics_event_id')
            ->where('analytics_events.app_id', $appId)
            ->delete();
        // Table: analytics_event_user_tag
        DB::table('analytics_event_user_tag')
            ->join('analytics_events', 'analytics_events.id', 'analytics_event_user_tag.analytics_event_id')
            ->where('analytics_events.app_id', $appId)
            ->delete();
        // Table: analytics_events
        AnalyticsEvent::where('app_id', $appId)->delete();

        $this->line('Deleting Azure videos…');
        // Table: azure_videos
        AzureVideo::where('app_id', $appId)->delete();

        $this->line('Deleting clone records…');
        // Table: clone_records
        CloneRecord::where('target_app_id', $appId)->delete();
        $cloneRecords = CloneRecord::all();

        foreach ($cloneRecords as $cloneRecord) {
            $toDelete = false;

            switch ($cloneRecord->type) {
                case MorphTypes::TYPE_NEWS:
                    $toDelete = News
                        ::where('app_id', $appId)
                        ->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
                case MorphTypes::TYPE_COMPETITION:
                    $toDelete = Competition
                        ::where('app_id', $appId)
                        ->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
                case MorphTypes::TYPE_CERTIFICATE:
                    $toDelete = CertificateTemplate::whereHas('test', function ($query) use ($appId) {
                        $query->where('app_id', $appId);
                    })->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
                case MorphTypes::TYPE_COURSE:
                    $toDelete = Course
                        ::where('app_id', $appId)
                        ->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
                case MorphTypes::TYPE_TEST:
                    $toDelete = Test
                        ::where('app_id', $appId)
                        ->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
                case MorphTypes::TYPE_QUESTION:
                    $toDelete = Question
                        ::where('app_id', $appId)
                        ->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
                case MorphTypes::TYPE_LEARNINGMATERIAL_FOLDER:
                    $toDelete = LearningMaterialFolder
                        ::where('app_id', $appId)
                        ->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
                case MorphTypes::TYPE_LEARNINGMATERIAL:
                    $toDelete = LearningMaterial::whereHas('learningMaterialFolder', function ($query) use ($appId) {
                        $query->where('app_id', $appId);
                    })->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
                case MorphTypes::TYPE_CATEGORY:
                    $toDelete = Category
                        ::where('app_id', $appId)
                        ->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
                case MorphTypes::TYPE_CATEGORYGROUP:
                    $toDelete = Categorygroup
                        ::where('app_id', $appId)
                        ->where('id', $cloneRecord->source_id)
                        ->exists();
                    break;
            }

            if($toDelete) {
                // Table: clone_records
                $cloneRecord->delete();
            }
        }

        $this->line('Deleting comments…');
        // Table: comment_reports
        CommentReport::whereHas('comment', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: comments
        Comment::where('app_id', $appId)->delete();

        $this->line('Deleting content categories…');
        // Table: content_category_translations
        ContentCategoryTranslation::whereHas('contentCategory', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: content_category_relations
        ContentCategoryRelation::whereHas('contentCategory', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: content_categories
        ContentCategory::where('app_id', $appId)->delete();

        $this->line('Deleting direct messages…');
        // Table: direct_messages
        DirectMessage::where('app_id', $appId)->delete();

        $this->line('Deleting user roles…');
        // Table: user_role_rights
        UserRoleRight::whereHas('userRole', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: user_roles
        UserRole::where('app_id', $appId)->delete();

        $this->line('Deleting keywords…');
        // Table: keyword_translations
        KeywordTranslation::whereHas('keyword', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: keywords
        Keyword::where('app_id', $appId)->delete();

        $this->line('Deleting privacy note confirmations…');
        // Table: privacy_note_confirmations
        PrivacyNoteConfirmation
            ::join('users', 'users.id', 'privacy_note_confirmations.user_id')
            ->where('users.app_id', $appId)
            ->delete();

        $this->line('Deleting fcm tokens…');
        // Table: fcm_tokens
        FcmToken
            ::join('users', 'users.id', 'fcm_tokens.user_id')
            ->where('users.app_id', $appId)
            ->delete();

        $this->line('Deleting open id tokens…');
        // Table: open_id_tokens
        OpenIdToken
            ::join('users', 'users.id', 'open_id_tokens.user_id')
            ->where('users.app_id', $appId)
            ->delete();

        $this->line('Deleting auth tokens…');
        // Table: auth_tokens
        AuthToken
            ::join('users', 'users.id', 'auth_tokens.user_id')
            ->where('users.app_id', $appId)
            ->delete();
        $this->line('Deleting personal access tokens…');
        // Table: personal_access_tokens
        DB::table('personal_access_tokens')
            ->join('users', 'users.id', 'personal_access_tokens.tokenable_id')
            ->where('users.app_id', $appId)
            ->where('personal_access_tokens.tokenable_type', MorphTypes::TYPE_USER)
            ->delete();

        $this->line('Deleting access logs…');
        // Table: access_logs
        AccessLog::whereHas('user', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();

        $this->line('Deleting webinars…');
        // Table: webinar_participants
        WebinarParticipant::whereHas('webinar', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: webinar_additional_users
        WebinarAdditionalUser::whereHas('webinar', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: webinars
        Webinar::where('app_id', $appId)->delete();

        $this->line('Deleting frontend translations…');
        // Table: frontend_translations
        FrontendTranslation
            ::join('app_profiles', 'app_profiles.id', 'frontend_translations.app_profile_id')
            ->where('app_profiles.app_id', $appId)
            ->delete();

        $this->line('Deleting app profile home components…');
        // Table: app_profile_home_components
        AppProfileHomeComponent
            ::join('app_profiles', 'app_profiles.id', 'app_profile_home_components.app_profile_id')
            ->where('app_profiles.app_id', $appId)
            ->delete();

        $this->line('Deleting mail notification user settings…');
        // Table: notification_user_settings
        UserNotificationSetting
            ::join('users', 'users.id', 'user_notification_settings.user_id')
            ->where('users.app_id', $appId)
            ->delete();

        $this->line('Deleting app settings…');
        // Table: app_settings
        AppSetting::where('app_id', $appId)->delete();

        $this->line('Deleting categories…');
        // Table: category_hiders
        CategoryHider::whereHas('category', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: category_translations
        CategoryTranslation::whereHas('category', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: categories
        Category::where('app_id', $appId)->delete();

        $this->line('Deleting category groups…');
        // Table: categorygroup_translations
        CategorygroupTranslation::whereHas('categorygroup', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: categorygroups
        Categorygroup::where('app_id', $appId)->delete();

        $this->line('Deleting competitions…');
        // Table: competitions
        Competition::where('app_id', $appId)->delete();

        $this->line('Deleting games…');
        // Table: game_points
        GamePoint::whereHas('user', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: game_question_answers
        GameQuestionAnswer::whereHas('user', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: game_questions
        GameQuestion::whereHas('question', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: game_rounds
        GameRound::whereHas('game', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: games
        Game::where('app_id', $appId)->delete();

        $this->line('Deleting quiz teams…');
        $quizTeams = QuizTeam::where('app_id', $appId);
        foreach ($quizTeams->get() as $quizTeam) {
            // Table: quiz_team_members
            $quizTeam->members()->detach();
        }
        // Table: quiz_teams
        $quizTeams->delete();

        $this->line('Deleting indexcards…');
        // Table: index_cards
        IndexCard::where('app_id', $appId)->delete();
        // Table: learn_box_cards
        LearnBoxCard::whereHas('user', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();

        $this->line('Deleting learning materials…');
        $learning_materials = LearningMaterial::whereHas('learningMaterialFolder', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        });
        // Table: learning_material_translations
        LearningMaterialTranslation::whereIn('learning_material_id', $learning_materials->pluck('id'))->delete();
        // Table: learning_materials
        $learning_materials->delete();
        $learning_material_folders = LearningMaterialFolder::where('app_id', $appId);
        // Table: learning_material_folder_translations
        LearningMaterialFolderTranslation::whereIn('learning_material_folder_id', $learning_material_folders->pluck('id'))->delete();
        // Table: learning_material_folders
        $learning_material_folders->delete();

        $this->line('Deleting mail templates…');
        // Table: mail_template_translations
        MailTemplateTranslation::whereHas('mailTemplate', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: mail_templates
        MailTemplate::where('app_id', $appId)->delete();

        $this->line('Deleting news…');
        // Table: news_translations
        NewsTranslation::whereHas('news', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: news
        News::where('app_id', $appId)->delete();

        $this->line('Deleting pages…');
        // Table: page_translations
        PageTranslation::whereHas('page', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: pages
        Page::where('app_id', $appId)->delete();

        $this->line('Deleting questions…');
        // Table: question_translations
        QuestionTranslation::whereHas('question', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: question_difficulties
        QuestionDifficulty::whereHas('question', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: question_attachments
        QuestionAttachment::whereHas('question', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: question_answer_translations
        QuestionAnswerTranslation::whereHas('questionAnswer', function ($query) use ($appId) {
            $query->whereHas('question', function ($query) use ($appId) {
                $query->where('app_id', $appId);
            });
        })->delete();
        // Table: question_answers
        QuestionAnswer::whereHas('question', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: questions
        Question::where('app_id', $appId)->delete();
        // Table: suggested_question_answers
        SuggestedQuestionAnswer::whereHas('suggestedQuestion', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: suggested_questions
        SuggestedQuestion::where('app_id', $appId)->delete();


        $this->line('Deleting reportings…');
        // Table: reportings
        Reporting::where('app_id', $appId)->delete();

        $this->line('Deleting tags…');
        $tags = Tag::where('app_id', $appId);

        $deleteTagFromTables = [
            'advertisement_tag',
            'appointment_tags',
            'app_profile_tags',
            'category_tag',
            'categorygroup_tag',
            'competition_tag',
            'course_award_tags',
            'course_preview_tag',
            'course_retract_tags',
            'course_tag',
            'form_tags',
            'learning_material_folder_tags',
            'learning_material_tags',
            'news_tag',
            'tag_user',
            'page_tag',
            'test_award_tags',
            'test_tags',
            'user_tag_rights',
            'webinar_tags',
            'voucher_tags',
        ];

        foreach ($deleteTagFromTables as $table) {
            DB::table($table)->whereIn('tag_id', $tags->pluck('id'))->delete();
        }

        // Table: tags
        $tags->delete();

        $this->Line('Deleting courses…');
        // Table: course_content_attempt_attachments
        CourseContentAttemptAttachment
            ::join('course_content_attempts', 'course_content_attempt_attachments.course_content_attempt_id', '=', 'course_content_attempts.id')
            ->join('course_participations', 'course_participations.id', '=', 'course_content_attempts.course_participation_id')
            ->join('courses', 'courses.id', '=', 'course_participations.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        // Table: course_content_attempts
        CourseContentAttempt
            ::join('course_participations', 'course_participations.id', '=', 'course_content_attempts.course_participation_id')
            ->join('courses', 'courses.id', '=', 'course_participations.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        // Table: course_participations
        CourseParticipation
            ::join('courses', 'courses.id', '=', 'course_participations.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        // Table: course_content_attachments
        CourseContentAttachment
            ::join('course_contents', 'course_content_attachments.course_content_id', '=', 'course_contents.id')
            ->join('course_chapters', 'course_contents.course_chapter_id', '=', 'course_chapters.id')
            ->join('courses', 'courses.id', '=', 'course_chapters.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        // Table: course_content_translations
        CourseContentTranslation
            ::join('course_contents', 'course_content_translations.course_content_id', '=', 'course_contents.id')
            ->join('course_chapters', 'course_contents.course_chapter_id', '=', 'course_chapters.id')
            ->join('courses', 'courses.id', '=', 'course_chapters.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        // Table: course_contents
        CourseContent
            ::join('course_chapters', 'course_contents.course_chapter_id', '=', 'course_chapters.id')
            ->join('courses', 'courses.id', '=', 'course_chapters.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        // Table: course_chapter_translations
        CourseChapterTranslation
            ::join('course_chapters', 'course_chapter_translations.course_chapter_id', '=', 'course_chapters.id')
            ->join('courses', 'courses.id', '=', 'course_chapters.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        // Table: course_chapters
        CourseChapter
            ::join('courses', 'courses.id', '=', 'course_chapters.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        // Table: course_translations
        CourseTranslation
            ::join('courses', 'courses.id', '=', 'course_translations.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        // Table: course_access_requests
        CourseAccessRequest
            ::join('courses', 'courses.id', '=', 'course_access_requests.course_id')
            ->where('courses.app_id', $appId)
            ->delete();
        $courses = Course::where('app_id', $appId);
        foreach ($courses->get() as $course) {
            // Table: course_managers
            $course->managers()->detach();
            // Table: course_template_inheritances
            $course->templateInheritanceApps()->detach();
        }
        // Table: courses
        $courses->delete();

        $this->line('Deleting app profile settings…');
        // Table: app_profile_settings
        AppProfileSetting
            ::join('app_profiles', 'app_profiles.id', 'app_profile_settings.app_profile_id')
            ->where('app_profiles.app_id', $appId)
            ->delete();
        // Table: app_profiles
        AppProfile
            ::where('app_id', $appId)
            ->delete();

        $this->line('Deleting tag groups…');
        // Table: tag_groups
        TagGroup::where('app_id', $appId)->delete();

        $this->line('Deleting tests…');
        // Table: test_questions
        TestQuestion::whereHas('test', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: test_submission_answers
        TestSubmissionAnswer::whereHas('testSubmission', function ($query) use ($appId) {
            $query->whereHas('test', function ($query) use ($appId) {
                $query->where('app_id', $appId);
            });
        })->delete();
        // Table: test_submissions
        TestSubmission::whereHas('test', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: test_translations
        TestTranslation::whereHas('test', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: test_categories
        TestCategory::whereHas('test', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        $certificateTemplateIds = CertificateTemplate::whereHas('test', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->pluck('id');
        // Table: certificate_template_translations
        CertificateTemplateTranslation::whereIn('certificate_template_id', $certificateTemplateIds)->delete();
        // Table: certificate_templates
        CertificateTemplate::whereIn('id', $certificateTemplateIds)->delete();
        // Table: tests
        Test::where('app_id', $appId)->delete();

        $this->line('Deleting app ratings…');
        // Table: app_ratings
        AppRating
            ::join('users', 'users.id', 'app_ratings.user_id')
            ->where('users.app_id', $appId)
            ->delete();

        $this->line('Deleting users…');
        // Table: likes
        Like
            ::join('users', 'users.id', '=', 'likes.user_id')
            ->where('users.app_id', $appId)
            ->delete();
        // Table: metafields
        UserMetafield
            ::join('users', 'users.id', '=', 'user_metafields.user_id')
            ->where('users.app_id', $appId)
            ->delete();
        // Table: viewcounts
        Viewcount
            ::join('users', 'users.id', '=', 'viewcounts.user_id')
            ->where('users.app_id', $appId)
            ->delete();
        // Table: training_answers
        TrainingAnswer::whereHas('user', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: user_permissions
        UserPermission::whereHas('user', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: event_histories
        EventHistory::whereHas('user', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: users
        User::where('app_id', $appId)->withoutGlobalScope('human')->delete();

        $this->line('Deleting vouchers…');
        // Table: voucher_codes
        VoucherCode::whereHas('voucher', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: vouchers
        Voucher::where('app_id', $appId)->delete();

        $this->line('Deleting imports…');
        // Table: imports
        Import::where('app_id', $appId)->delete();

        $this->line('Deleting reminders…');
        // Table: reminders_metadata
        ReminderMetadata::whereHas('reminder', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: reminders
        Reminder::where('app_id', $appId)->delete();

        $this->line('Deleting advertisements…');
        // Table: advertisement_positions
        AdvertisementPosition::whereHas('advertisement', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: advertisement_translations
        AdvertisementTranslation::whereHas('advertisement', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: advertisements
        Advertisement::where('app_id', $appId)->delete();

        $this->line('Deleting appointments…');
        // Table: appointment_translations
        AppointmentTranslation::whereHas('appointment', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->delete();
        // Table: appointments
        Appointment::where('app_id', $appId)->delete();

        $this->line('Deleting forms…');
        $forms = Form::where('app_id',$appId);
        $formAnswers = FormAnswer::whereIn('form_id',$forms->pluck('id'));
        //Table: form_answer_fields
        FormAnswerField
            ::wherein ('form_answer_id',$formAnswers->pluck('id'))->delete();
        //Table: form_answers
        $formAnswers->delete();
        $formFields = FormField::whereIn('form_id',$forms->pluck('id'));
        //Table: form_field_translations
        FormFieldTranslation
            ::whereIn('form_field_id',$formFields->pluck('id'))->delete();
        //Table: form_fields
        $formFields->delete();
        //Table: form_translations
        FormTranslation
            ::whereIn('form_id',$forms->pluck('id'))->delete();
        //Table: forms
        $forms->delete();

        if ($app) {
            // Table: apps
            $app->delete();
        }

        $this->info('All done!');
    }
}
