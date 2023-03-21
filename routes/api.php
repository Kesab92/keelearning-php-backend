<?php
Route::post('/deepstreamlogin', 'Api\AuthController@deepstreamlogin');
if (!live()) {
    Route::get('/opcache-reset', 'Api\DevController@resetOpcache');
}

Route::middleware('throttle:20,1')->group(function () {
    Route::get('/healthcheck', 'Api\HealthCheckController@healthcheck');
});

Route::middleware('throttle:frontendAuth')->group(function () {
    Route::post('/login', 'Api\AuthController@postLogin');
    Route::post('/signup', 'Api\AuthController@postSignup')->middleware('concurrent');
    Route::post('/tmpaccount', 'Api\AuthController@tmpAccount');
    Route::post('/reset-password', 'Api\AuthController@resetPassword');
    Route::post('/nexus-kis/login', 'Api\Custom\NexusKisController@login');

    Route::get('/openid/getAuthUrl/{profile_id}/{native_app_id?}', 'Api\OpenIdController@getAuthUrl');
    Route::post('/openid/token', 'Api\OpenIdController@receiveToken')->name('openid.receiveToken');
    Route::post('/openid/tokenLogin', 'Api\OpenIdController@tokenLogin');

    Route::post('/tmp-login-token', 'Api\TmpTokenLoginController@tokenLogin');
});

Route::middleware('throttle:frontend')->group(function () {

    Route::get('/login/contact/{app_id}/{profile_id?}', 'Api\AuthController@getContact');
    Route::get('/check-slug', 'Api\AuthController@checkSlug');
    Route::get('/signup/tags/{app_id}', 'Api\AuthController@getSelectableTagGroups');
    Route::get('/signup/{app_id}/{profile_id?}', 'Api\AuthController@getSignupData');
    Route::get('/public/pages/auth/{app_id}/{profile_id?}', 'Api\PagesController@publicAuthPages');
    Route::get('/public/pages/{page_id}/{profile_id?}', 'Api\PagesController@publicPage');
    Route::get('/appconfig/{slug?}/manifest.webmanifest', 'Api\AppConfigController@getWebmanifest');
    Route::get('/appconfig/{hostname}', 'Api\AppConfigController@getConfig');
    Route::get('/curator/tests', 'Api\Custom\CuratorController@tests');

    /* Advertisements */
    // This route must be public, because it's called from the login page as well as within the app
    Route::get('/advertisements', 'Api\AdvertisementsController@advertisements');
    Route::get('/visual-highlights', 'Api\AdvertisementsController@advertisements'); // This route is necessary, so we can access advertisements without triggering an adblocker. It's exactly the same as the route above.

    Route::middleware('signed')->group(function () {
        Route::get('/tests/certificate/{submission_id}', 'Api\TestsController@certificate')->name('certificateDownload');
        Route::get('/courses/{course_id}/participations/{participation_id}/certificate/{attempt_id}', 'Api\CourseParticipationsController@certificate')->name('courseCertificateDownload');
        Route::get('/users/{userId}/confirm-email/{email}', 'Api\UsersController@confirmEmailChange')->name('emailChangeConfirm');
        Route::get('/appointments/{appointmentId}/ics/{language}', 'Api\AppointmentsController@getIcsFile')->name('appointmentIcsFile');
        Route::get('/notification-subscriptions/{userId}/{foreignType}/{foreignId}', 'Api\NotificationSubscriptionsController@unsubscribe')->name('notification-subscriptions.unsubscribe');
    });

    Route::middleware('auth.active')->group(function () {

        /* Terms of Service */
        // If you update the tos routes make sure to also update the ActiveMiddleware to allow all users access
        Route::post('/accept-tos', 'Api\AuthController@acceptToS');
        Route::get('/tos', 'Api\PagesController@getToS');

        Route::post('/logout', 'Api\AuthController@logout');

        Route::post('/password/force-password-reset', 'Api\AuthController@forcePasswordReset');

        Route::get('/tmp-login-token', 'Api\TmpTokenLoginController@getTmpToken');

        Route::middleware('auth.resetpassword')->group(function () {
            /* Account convertion */
            Route::post('/convertaccount', 'Api\AuthController@convertAccount');
            Route::post('/addmail', 'Api\AuthController@addMail');

            /* FCM */
            Route::post('/setfcmid', 'Api\AuthController@setFCMId');
            Route::post('/fcm/token', 'Api\FCMController@addFCMToken');

            /* GCM */
            Route::post('/setgcmid', 'Api\AuthController@setFCMId');
            Route::post('/setgcmauth', 'Api\AuthController@setGCMAuth');

            /* User */
            Route::get('/user/tags', 'Api\UsersController@tags');
            Route::get('/user/tagsWithGroups', 'Api\UsersController@tagsWithGroups');
            Route::get('/user/allTagsWithGroups', 'Api\UsersController@allTagsWithGroups');
            Route::get('/user/categories', 'Api\UsersController@getCategories');
            Route::post('/user/language', 'Api\UsersController@setLanguage');

            /* Users */
            Route::get('/users/bots', 'Api\UsersController@getBots');
            Route::get('/users/search', 'Api\UsersController@findUser');
            Route::get('/users/random', 'Api\UsersController@getRandomOpponent');
            /**
             * @TODO Should be account/request-deletion but not now because of legacy reasons
             */
            Route::post('users/request-deletion', 'Api\UsersController@requestUserDeletion');
            Route::get('/users/{user_id}', 'Api\UsersController@getUser');
            Route::post('users/{userId}', 'Api\UsersController@update');

            /* Direct messages */
            Route::get('/direct-messages', 'Api\DirectMessagesController@directMessages');
            Route::post('/direct-messages/{id}/mark-as-read', 'Api\DirectMessagesController@markAsRead');

            /* Profile */
            Route::get('profile', 'Api\ProfileController@getUserProfile');
            Route::post('profile/avatar', 'Api\ProfileController@setAvatar');
            Route::post('/password', 'Api\AuthController@setPassword');
            Route::post('/password/set', 'Api\AuthController@setInsecurePassword');
            Route::get('profile/default-avatars', 'Api\ProfileController@getDefaultAvatars');

            /* Competitions */
            Route::get('competitions', 'Api\CompetitionsController@index');
            Route::get('competitions/hasRequiredCredentials', 'Api\CompetitionsController@hasRequiredCredentialsForCompetitions');
            Route::get('competitions/{id}', 'Api\CompetitionsController@show');

            /* Games */
            Route::post('/games', 'Api\GamesController@createGame');
            Route::get('/games/{game_id}', 'Api\GamesController@getGame');
            Route::get('/games/active', 'Api\GamesController@getActiveGames');
            Route::get('/games/recent', 'Api\GamesController@getRecentGames');
            Route::get('/games/history', 'Api\GamesController@getHistory');
            Route::get('/games/{game_id}/question', 'Api\GamesController@getNextQuestion');
            Route::get('/games/{game_id}/categories', 'Api\GamesController@getAvailableCategories');
            Route::get('/games/{game_id}/intro', 'Api\GamesController@getIntro');
            Route::post('/games/{game_id}/categories', 'Api\GamesController@setNextCategory');
            Route::post('/games/{game_id}/question', 'Api\GamesController@answerQuestion');
            Route::post('/games/{game_id}/joker', 'Api\GamesController@useJoker');

            /* Webinars */
            Route::get('/webinars', 'Api\WebinarsController@webinars');
            Route::get('/webinars/recordings', 'Api\WebinarsController@recordings');
            Route::post('/webinars/{webinar_id}/join', 'Api\WebinarsController@join');

            /* Pages */
            Route::get('/pages', 'Api\PagesController@pages');
            Route::get('/pages/{page_id}', 'Api\PagesController@page');

            /* NotificationUserSettings */
            Route::get('/notification-user-settings', 'Api\NotificationSettingsController@getSettings');
            Route::post('/notification-user-settings', 'Api\NotificationSettingsController@updateSettings');
            /**
             * @deprecated
             */
            Route::get('/mail-notification-user-settings', 'Api\NotificationSettingsController@getSettingsLegacy');
            /**
             * @deprecated
             */
            Route::post('/mail-notification-user-settings', 'Api\NotificationSettingsController@updateSettingsLegacy');

            /* News */
            Route::get('/news', 'Api\NewsController@news');
            Route::get('/news/{id}', 'Api\NewsController@getEntryById');

            /* Learning Materials */
            Route::get('/learning-materials', 'Api\LearningMaterialsController@list');
            Route::get('/learning-materials/wbt-events', 'Api\LearningMaterialsController@allWbtEvents');
            Route::get('/learning-materials/{material_id}', 'Api\LearningMaterialsController@show');
            Route::get('/learning-materials/{material_id}/wbt-events', 'Api\LearningMaterialsController@wbtEvents');

            Route::get('/stats/players', 'Api\StatsController@players');
            Route::get('/stats/quiz-teams', 'Api\StatsController@quizTeams');
            /**
             * @deprecated
             */
            Route::get('/stats/groups', 'Api\StatsController@quizTeams');
            Route::get('/stats/mine', 'Api\StatsController@mine');
            Route::get('/stats/position', 'Api\StatsController@position');
            Route::get('/stats/position/{user_id}', 'Api\StatsController@userPosition');

            /* Groups */
            /**
             * @deprecated
             */
            Route::get('/groups', 'Api\QuizTeamsController@quizTeams');
            /**
             * @deprecated
             */
            Route::get('/groups/stats', 'Api\QuizTeamsController@quizTeamsWithStats');
            /**
             * @deprecated
             */
            Route::get('/groups/{group_id}', 'Api\QuizTeamsController@quizTeam');
            /**
             * @deprecated
             */
            Route::post('/groups', 'Api\QuizTeamsController@QuizTeamsController');
            /**
             * @deprecated
             */
            Route::post('/groups/{group_id}/members', 'Api\QuizTeamsController@addMember');
            /**
             * @deprecated
             */
            Route::post('/groups/{group_id}/members/remove', 'Api\QuizTeamsController@removeMember');

            /* Teams (new implementation of groups) */
            /**
             * @deprecated
             */
            Route::post('/teams', 'Api\QuizTeamsController@create');
            /**
             * @deprecated
             */
            Route::get('/teams/mine', 'Api\QuizTeamsController@mineWithOldFormat');
            /**
             * @deprecated
             */
            Route::get('/teams/checkTeamName', 'Api\QuizTeamsController@checkQuizTeamName');
            /**
             * @deprecated
             */
            Route::get('/teams/{team_id}', 'Api\QuizTeamsController@show');

            /* Teams (new implementation of quiz teams) */
            Route::post('quiz-teams', 'Api\QuizTeamsController@create');
            Route::get('quiz-teams/mine', 'Api\QuizTeamsController@mine');
            Route::get('quiz-teams/checkQuizTeamName', 'Api\QuizTeamsController@checkQuizTeamName');
            Route::get('quiz-teams/{team_id}', 'Api\QuizTeamsController@show');
            Route::post('quiz-teams/{team_id}/members', 'Api\QuizTeamsController@addMember');

            Route::get('/likes/{foreign_type}/{foreign_id}/likesIt', 'Api\LikesController@likesIt');
            Route::post('/likes/{foreign_type}/{foreign_id}/like', 'Api\LikesController@like');
            Route::post('/likes/{foreign_type}/{foreign_id}/dislike', 'Api\LikesController@dislike');

            /* Questions */
            Route::post('/questions/suggest', 'Api\QuestionsController@suggestQuestion');
            Route::get('/questions/suggestionSettings', 'Api\QuestionsController@suggestionSettings');
            Route::get('/questions/{id}/userData', 'Api\QuestionsController@getUserData');
            Route::post('/questions/{id}/userData', 'Api\QuestionsController@storeUserData');

            /* Training */
            Route::get('/training/categories', 'Api\TrainingController@categories');
            Route::get('/training/category/{category_id}/questions', 'Api\TrainingController@getQuestions');
            Route::post('/training/saveAnswer/{question_id}', 'Api\TrainingController@saveAnswer');
            Route::get('/training/stats', 'Api\TrainingController@stats');

            /* Learning */
            Route::get('/learning', 'Api\LearningController@allData');
            Route::post('/learning/save', 'Api\LearningController@saveData');
            Route::get('/learning/categories', 'Api\LearningController@categories');
            Route::get('/learning/statsData', 'Api\LearningController@statsData');
            Route::get('/learning/category/{category_id}/question', 'Api\LearningController@getQuestion');
            Route::get('/learning/category/{category_id}/question/free', 'Api\LearningController@getFreeQuestion');
            Route::post('/learning/saveAnswer/{question_id}', 'Api\LearningController@saveAnswer');
            Route::post('/learning/checkAnswer/{question_id}', 'Api\LearningController@checkAnswer');

            /* IndexCards */
            Route::get('/indexcards', 'Api\IndexCardsController@cards');
            Route::get('/indexcards/categories', 'Api\IndexCardsController@categories');
            Route::post('/indexcards/update', 'Api\IndexCardsController@update');
            Route::get('/indexcards/savedata', 'Api\IndexCardsController@savedata');

            /* Tests */
            Route::get('/tests', 'Api\TestsController@tests');
            Route::get('/tests-results', 'Api\TestsController@testsWithResults');
            Route::get('/tests/results', 'Api\TestsController@testResults');
            Route::get('/tests/{test_id}', 'Api\TestsController@test');
            Route::get('/tests/{test_id}/details', 'Api\TestsController@testDetails');
            Route::get('/tests/{test_id}/currentquestion', 'Api\TestsController@currentQuestion');
            Route::post('/tests/{test_id}/answer', 'Api\TestsController@saveAnswer');
            Route::get('/tests/results/{submission_id}', 'Api\TestsController@results');
            Route::get('/tests/resultsWithAnswers/{submission_id}', 'Api\TestsController@resultsWithAnswers');

            /* Vouchers */
            Route::get('/vouchers', 'Api\VouchersController@findVouchersByAuthenticatedUser');
            Route::post('/vouchers/redeem', 'Api\VouchersController@redeemCode');

            /* Feedback */
            Route::get('/feedback/ratingStatus', 'Api\FeedbackController@getRatingStatus');
            Route::post('/feedback/rate', 'Api\FeedbackController@createRating');
            Route::post('/feedback/send', 'Api\FeedbackController@sendFeedback');

            /* Analytics */
            Route::get('/analytics/challenging-questions', 'Api\AnalyticsController@challengingQuestions');
            Route::get('/analytics/nemesis-players', 'Api\AnalyticsController@nemesisPlayers');
            Route::get('/analytics/quiz-progress', 'Api\AnalyticsController@quizProgress');
            Route::get('/analytics/strong-players', 'Api\AnalyticsController@strongPlayers');

            /* Views */
            Route::post('/view/home', 'Api\ViewController@viewHome');
            Route::post('/view/learning-material', 'Api\ViewController@viewLearningMaterial');
            Route::post('/view/news', 'Api\ViewController@viewNews');

            /* Courses */
            Route::get('/courses', 'Api\CoursesController@courses');
            Route::get('/courses/{course_id}/contents', 'Api\CoursesController@courseContents');
            Route::get('/courses/{course_id}/contents/{content_id}/wbt-events', 'Api\CoursesController@courseContentWbtEvents');
            Route::post('/courses/{course_id}/access', 'Api\CourseAccessController@requestAccess');
            Route::post('/courses/{course_id}/participations', 'Api\CourseParticipationsController@create');
            Route::post('/courses/{course_id}/participations/{participation_id}', 'Api\CourseParticipationsController@show');
            Route::post('/courses/{course_id}/participations/{participation_id}/passed/{content_id}', 'Api\CourseParticipationsController@markContentAsPassed');
            Route::post('/courses/{course_id}/participations/{participation_id}/repeat/{content_id}', 'Api\CourseParticipationsController@repeatAttempt');
            Route::post('/courses/{course_id}/participations/{participation_id}/answer-question/{content_id}/{question_id}', 'Api\CourseParticipationsController@answerQuestion');
            Route::get('/courses/{course_id}/participations/{participation_id}/correct-answers/{content_id}/{question_id}', 'Api\CourseParticipationsController@getCorrectAnswers');
            Route::post('/courses/{course_id}/participations/{participation_id}/submit-form/{content_id}/{form_id}', 'Api\CourseParticipationsController@submitForm');

            /* Question preview */
            Route::get('/questions/{id}/preview', 'Api\QuestionsController@preview');

            /* Keywords */
            Route::get('/keywords', 'Api\KeywordsController@keywords');
            Route::get('/keywords/descriptions', 'Api\KeywordsController@descriptions');

            /* Appointments */
            Route::get('/appointments', 'Api\AppointmentsController@getAllAppointments');

            /* Todolists */
            Route::get('/todolists', 'Api\TodolistsController@getAllTodolists');
            Route::get('/todolists/all-item-answers', 'Api\TodolistsController@getAllItemAnswers');
            Route::post('/todolists/set-item/{id}', 'Api\TodolistsController@setItemAnswer');
        });

        /* Comments */
        Route::get('/comments/resource/{type}/{id}', 'Api\CommentsController@commentsForResource');
        Route::post('/comments/resource/{type}/{id}', 'Api\CommentsController@store');
        Route::delete('/comments/{id}', 'Api\CommentsController@delete');
        Route::post('/comments/{id}/report', 'Api\CommentsController@report');

        /* Forms */
        Route::get('/forms/{foreignType}/{foreignId}/answer', 'Api\FormsController@getAnswerByRelatable');

    });

});
