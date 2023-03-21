<?php

namespace App\Services;

use App\Models\Advertisements\Advertisement;
use App\Models\App;
use App\Models\Appointments\Appointment;
use App\Models\Category;
use App\Models\Categorygroup;
use App\Models\CertificateTemplate;
use App\Models\Comments\Comment;
use App\Models\Competition;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttachment;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Forms\Form;
use App\Models\Forms\FormField;
use App\Models\Game;
use App\Models\Keywords\Keyword;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\MailTemplate;
use App\Models\News;
use App\Models\Page;
use App\Models\Question;
use App\Models\SuggestedQuestion;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\Todolist;
use App\Models\TodolistItem;
use App\Models\User;

class MorphTypes
{
    const TYPE_LEARNINGMATERIAL = 1;
    const TYPE_NEWS = 2;
    const TYPE_COMPETITION = 3;
    const TYPE_CERTIFICATE = 4;
    const TYPE_COURSE = 5;
    const TYPE_TEST = 6;
    const TYPE_TEST_QUESTION = 7;
    const TYPE_QUESTION = 10;
    const TYPE_LEARNINGMATERIAL_FOLDER = 12;
    const TYPE_CATEGORY = 13;
    const TYPE_CATEGORYGROUP = 14;
    const TYPE_GAME = 15;
    const TYPE_APP = 16;
    const TYPE_COMMENT = 17;
    const TYPE_SUGGESTED_QUESTION = 18;
    const TYPE_FORM = 19;
    const TYPE_APPOINTMENT = 20;
    const TYPE_TODOLIST = 21;
    const TYPE_PAGE = 22;
    const TYPE_FORM_FIELD = 23;
    const TYPE_ADVERTISEMENT = 24;
    const TYPE_CONTENT_CATEGORY = 25;

    // Not currently used by any morphTo relations
    const TYPE_COURSE_CHAPTER = 1000;
    const TYPE_COURSE_CONTENT_QUESTIONS = 1001;
    const TYPE_COURSE_CONTENT_ATTEMPT = 1002;
    const TYPE_COURSE_CONTENT = 1003;
    const TYPE_COURSE_CONTENT_ATTACHMENT = 1004;
    const TYPE_TODOLIST_ITEM = 1100;
    const TYPE_MAIL_TEMPLATE = 1200;
    const TYPE_KEYWORD = 1300;

    const TYPE_USER = 2000;

    const MAPPING = [
        App::class => self::TYPE_APP,
        Advertisement::class => self::TYPE_ADVERTISEMENT,
        Appointment::class => self::TYPE_APPOINTMENT,
        Category::class => self::TYPE_CATEGORY,
        Categorygroup::class => self::TYPE_CATEGORYGROUP,
        CertificateTemplate::class => self::TYPE_CERTIFICATE,
        Comment::class => self::TYPE_COMMENT,
        Competition::class => self::TYPE_COMPETITION,
        Course::class => self::TYPE_COURSE,
        CourseChapter::class => self::TYPE_COURSE_CHAPTER,
        CourseContent::class => self::TYPE_COURSE_CONTENT,
        CourseContentAttachment::class => self::TYPE_COURSE_CONTENT_ATTACHMENT,
        CourseContentAttempt::class => self::TYPE_COURSE_CONTENT_ATTEMPT,
        ContentCategory::class => self::TYPE_CONTENT_CATEGORY,
        Form::class => self::TYPE_FORM,
        FormField::class => self::TYPE_FORM_FIELD,
        Game::class => self::TYPE_GAME,
        Keyword::class => self::TYPE_KEYWORD,
        LearningMaterial::class => self::TYPE_LEARNINGMATERIAL,
        LearningMaterialFolder::class => self::TYPE_LEARNINGMATERIAL_FOLDER,
        MailTemplate::class => self::TYPE_MAIL_TEMPLATE,
        News::class => self::TYPE_NEWS,
        Page::class => self::TYPE_PAGE,
        Question::class => self::TYPE_QUESTION,
        SuggestedQuestion::class => self::TYPE_SUGGESTED_QUESTION,
        Test::class => self::TYPE_TEST,
        TestQuestion::class => self::TYPE_TEST_QUESTION,
        Todolist::class => self::TYPE_TODOLIST,
        TodolistItem::class => self::TYPE_TODOLIST_ITEM,
        User::class => self::TYPE_USER,
    ];
}
