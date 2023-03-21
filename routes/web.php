<?php

Route::group(['middleware' => ['web', 'throttle:150,1']], function () {
    Route::get('/login', 'Backend\Auth\AuthController@getLogin');
    Route::get('/login/apps', 'Backend\Auth\AuthController@getApps');
    Route::post('/login', 'Backend\Auth\AuthController@postLogin');
    Route::get('/password-reset', 'Backend\Auth\AuthController@getPasswordReset');
    Route::post('/password-reset', 'Backend\Auth\AuthController@postPasswordReset');
    Route::get('/logout', 'Backend\Auth\AuthController@getLogout');
    Route::get('/users/account-activation/{userId}', 'Backend\Auth\AuthController@activateAccount')
        ->name('account-activation');
});

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::get('/', 'Backend\DashboardController@index');

    Route::get('/appswitcher', 'Backend\SuperadminController@appswitcher');
    Route::get('/setapp/{newapp}', 'Backend\SuperadminController@setapp');
    Route::get('/superadmin/user-activity', 'Backend\SuperadminController@userActivity');
    Route::get('/superadmin/user-activity/download', 'Backend\SuperadminController@userActivityDownload');

    Route::get('/setlang/{newlang}', 'Backend\Auth\AuthController@setlang');
    Route::get('/settings', 'Backend\SettingsController@index');
    Route::post('/settings/edit/{id}', 'Backend\SettingsController@update');

    Route::get('/questions', 'Backend\QuestionsController@index');
    Route::get('/questions/export', 'Backend\QuestionsController@export');
    Route::get('/questions/download/from/{from}/to/{to}', 'Backend\QuestionsExportController@export');
    Route::post('/questions/import', 'Backend\QuestionsExportController@import');
    Route::post('/questions/import/check/{language}', 'Backend\QuestionsExportController@checkImport');
    Route::post('/questions', 'Backend\QuestionsController@create');
    Route::get('/questions/{id}', 'Backend\QuestionsController@edit');
    Route::post('/questions/{id}', 'Backend\QuestionsController@update');
    Route::post('/questions/{id}/attachments', 'Backend\QuestionsController@addAttachment');
    Route::post('/questions/activateMultiple', 'Backend\QuestionsController@activateMultiple');
    Route::post('/questions/invisibleMultiple', 'Backend\QuestionsController@invisibleMultiple');
    Route::post('/questions/deleteMultiple', 'Backend\QuestionsController@deleteMultiple');
    Route::post('/questions/deleteInformation', 'Backend\QuestionsController@getDeleteInformation');
    Route::get('/questions/{id}/delete', 'Backend\QuestionsController@delete');
    Route::get('/questions/{questionId}/attachments/{attachmentId}/delete', 'Backend\QuestionsController@deleteAttachment');

    Route::get('/suggested-questions', 'Backend\SuggestedQuestionsController@index');

    Route::get('/users', 'Backend\UsersController@index');
    Route::get('/users/{id}/avatar', 'Backend\UsersController@redirectToAvatar');
    Route::get('/users/{id}/qualification-history', 'Backend\UserQualificationHistoryController@export');
    Route::get('/users/export', 'Backend\UsersController@export');

    Route::get('/indexcards', 'Backend\IndexCardsController@index');
    Route::post('/indexcards', 'Backend\IndexCardsController@create');
    Route::get('/indexcards/export', 'Backend\IndexCardsController@export');
    Route::get('/indexcards/{id}', 'Backend\IndexCardsController@edit');
    Route::post('/indexcards/{id}', 'Backend\IndexCardsController@update');
    Route::post('/indexcards/{id}/image', 'Backend\IndexCardsController@image');
    Route::get('/indexcards/{id}/deleteimage', 'Backend\IndexCardsController@deleteImage');
    Route::get('/indexcards/{id}/delete', 'Backend\IndexCardsController@delete');

    Route::get('/categories', 'Backend\CategoriesController@index');

    Route::get('/courses', 'Backend\CoursesController@index');
    Route::get('/courses/{id}', 'Backend\CoursesController@show');
    Route::get('/courses/certificates/{content_id}/preview', 'Backend\CoursesController@certificatePreview');

    Route::get('/course-statistics/{id}', 'Backend\CourseStatisticsController@show');
    Route::get('/course-statistics/{id}/export/overall', 'Backend\CourseStatisticsController@exportOverall');
    Route::get('/course-statistics/{id}/export/test/{contentId}', 'Backend\CourseStatisticsController@exportTest');
    Route::get('/course-statistics/{id}/export/form/{courseContentId}', 'Backend\CourseStatisticsController@exportForm');
    Route::get('/course-statistics/{id}/export/wbt/{contentId}', 'Backend\CourseStatisticsController@exportWbt');

    Route::get('/stats/users', 'Backend\StatsUsersController@index');
    Route::get('/stats/users/export', 'Backend\StatsUsersController@export');

    Route::get('/stats/quiz', 'Backend\StatsQuizController@index')->name('stats.quiz');
    Route::get('/stats/quiz/csv/players', 'Backend\StatsQuizCSVController@players');
    Route::get('/stats/quiz/csv/questions', 'Backend\StatsQuizCSVController@questions');
    Route::get('/stats/quiz/csv/categories', 'Backend\StatsQuizCSVController@categories');
    Route::get('/stats/quiz/csv/quiz-teams', 'Backend\StatsQuizCSVController@quizTeams');
    Route::get('/stats/quiz/reporting', 'Backend\StatsQuizReportingController@overview');
    Route::post('/stats/quiz/reporting', 'Backend\StatsQuizReportingController@store');

    Route::get('/stats/training', 'Backend\StatsTrainingController@players')->name('stats.training');
    Route::get('/stats/training/players', 'Backend\StatsTrainingController@players')->name('stats.training.players');
    Route::get('/stats/training/csv/players', 'Backend\StatsTrainingCSVController@players');
    Route::get('/stats/views', 'Backend\StatsViewsController@index')->name('stats.views');
    Route::get('/stats/views/csv', 'Backend\StatsViewsController@csv')->name('stats.views.csv');
    Route::get('/stats/wbt', 'Backend\StatsWbtController@index')->name('stats.wbt');

    Route::get('/stats/byTagForCategory', 'Backend\BaldingerController@index');

    Route::get('/pages', 'Backend\PagesController@index');

    Route::get('/mails', 'Backend\MailsController@index');
    Route::get('/mails/{type}', 'Backend\MailsController@edit');
    Route::post('/mails/{type}', 'Backend\MailsController@update');

    Route::get('/news', 'Backend\NewsController@index');

    Route::get('/misc/faq', 'Backend\MiscController@faq');
    Route::post('/misc/faq', 'Backend\MiscController@faqImageUpload');
    Route::get('/misc/faq/add', 'Backend\MiscController@addFaq');
    Route::post('/misc/faq/{id}', 'Backend\MiscController@updateFaq');
    Route::post('/misc/faq/{id}/remove', 'Backend\MiscController@removeFaq');

    Route::get('/competitions', 'Backend\CompetitionsController@index');
    Route::post('/competitions', 'Backend\CompetitionsController@create');
    Route::get('/competitions/{id}', 'Backend\CompetitionsController@details');
    Route::get('/competitions/{id}/download', 'Backend\CompetitionsController@download');
    Route::get('/competitions/{id}/refresh', 'Backend\CompetitionsController@refresh');
    Route::post('/competitions/{id}/update', 'Backend\CompetitionsController@update');
    Route::post('/competitions/{id}/harddelete', 'Backend\CompetitionsController@harddelete');
    Route::post('/competitions/{id}/upload/cover', 'Backend\CompetitionsController@uploadCoverImage');
    Route::get('/competitions/{id}/removecover', 'Backend\CompetitionsController@removeCoverImage');

    Route::get('/import', 'Backend\ImportController@index');
    Route::get('/import/questions', 'Backend\ImportController@questionsImport');
    Route::get('/import/users', 'Backend\ImportController@usersImport');
    Route::get('/import/cards', 'Backend\ImportController@cardsImport');
    Route::get('/import/delete-users', 'Backend\ImportController@usersDeletion');
    Route::get('/import/examples/{type}', 'Backend\ImportController@createExampleFile');

    Route::get('/tags', 'Backend\TagsController@index');

    Route::get('/tag-groups', 'Backend\TagGroupsController@index');

    Route::get('/tests', 'Backend\TestsController@index');
    Route::post('/tests', 'Backend\TestsController@create');
    Route::get('/tests/{test_id}', 'Backend\TestsController@view');
    Route::get('/tests/{test_id}/reminders', 'Backend\TestsController@reminders');
    Route::get('/tests/{test_id}/results', 'Backend\TestsController@results');
    Route::get('/tests/{test_id}/resultscsv', 'Backend\TestsController@resultscsv');
    Route::get('/tests/{test_id}/answers/{user_id}', 'Backend\TestsController@downloadUserAnswers');
    Route::get('/tests/{test_id}/results-history-csv', 'Backend\TestsController@resultsHistoryCSV');
    Route::get('/tests/{test_id}/certificates', 'Backend\TestsController@certificateDesigner');
    Route::get('/tests/{test_id}/certificates/preview', 'Backend\TestsController@certificatePreview');
    Route::get('/tests/{test_id}/render/{testSubmissionId}', 'Backend\TestsController@renderSubmissionPDF');
    Route::post('/tests/{test_id}/remove', 'Backend\TestsController@remove');
    Route::post('/tests/{test_id}/archive', 'Backend\TestsController@archive');
    Route::post('/tests/{test_id}/deleteInformation', 'Backend\TestsController@getDeleteInformation');

    Route::get('/jobs', 'Backend\JobsController@listRunningJobs');
    Route::get('/jobs/active', 'Backend\JobsController@listRunningJobs');

    Route::get('/learningmaterials', 'Backend\LearningMaterialsController@index');

    Route::get('/advertisements', 'Backend\AdvertisementsController@index');

    Route::get('/logs/{day?}', 'Backend\LogsController@overview');

    Route::get('/accesslogs', 'Backend\AccessLogsController@overview');

    // new vue routes
    Route::get('/help', 'Backend\HelpDeskController@index');
    Route::get('/help/knowledge', 'Backend\HelpDeskController@knowledge');
    Route::get('/help/faq', 'Backend\HelpDeskController@faq');

    Route::get('/vouchers', 'Backend\VouchersController@index');
    Route::get('/vouchers/{id}/codes', 'Backend\VouchersController@downloadVoucherCodes');

    Route::get('/webinars', 'Backend\WebinarsController@index');

    Route::get('/stats/ratings', 'Backend\RatingsController@index');

    Route::get('/keywords', 'Backend\KeywordsController@index');

    Route::get('/comments', 'Backend\CommentsController@index');

    Route::get('/quiz-teams', 'Backend\QuizTeamsController@index');
    Route::get('/reports/{reportType}/export', 'Backend\ReportsController@export');

    Route::get('/appointments', 'Backend\AppointmentsController@index');

    Route::get('/forms', 'Backend\FormsController@index');
});
