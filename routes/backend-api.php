<?php

Route::middleware('admin')->group(function () {
    Route::get('/language/config', 'LanguageController@getLanguageConfig');

    Route::post('/questions', 'QuestionsController@create');
    Route::get('/questions/list', 'QuestionsController@index');
    Route::get('/questions/{id}', 'QuestionsController@show');
    Route::post('/questions/{id}', 'QuestionsController@update');
    Route::delete('/questions/{id}', 'QuestionsController@delete');
    Route::get('/questions/{id}/delete-information', 'QuestionsController@deleteInformation');
    Route::post('/questions/{id}/upload-attachment', 'QuestionsController@uploadAttachment');
    Route::post('/questions/{id}/delete-attachment', 'QuestionsController@deleteAttachment');
    Route::post('/questions/search', 'QuestionsController@search');
    Route::post('/questions/activateMultiple', 'QuestionsController@activateMultiple');
    Route::post('/questions/deactivateMultiple', 'QuestionsController@deactivateMultiple');
    Route::post('/questions/deleteMultipleInformation', 'QuestionsController@deleteMultipleInformation');
    Route::post('/questions/deleteMultiple', 'QuestionsController@deleteMultiple');

    Route::get('/helpdesk/knowledge', 'HelpDeskCategoriesController@findCategoriesWithPages');
    Route::get('/helpdesk/faq', 'HelpDeskPagesController@findFAQPages');
    Route::get('/helpdesk/counts', 'HelpDeskCategoriesController@findCounts');
    Route::get('/helpdesk/knowledge/{id}', 'HelpDeskPagesController@findPage');
    Route::get('/helpdesk/{keyword}/query', 'HelpDeskPagesController@searchPages');
    Route::get('/helpdesk/support-info', 'HelpDeskPagesController@supportInfo');

    Route::post('/helpdesk/knowledge/sort', 'HelpDeskCategoriesController@updateSortOrder');
    Route::post('/helpdesk/knowledge/categories', 'HelpDeskCategoriesController@store');
    Route::post('/helpdesk/knowledge/categories/{id}', 'HelpDeskCategoriesController@update');
    Route::post('/helpdesk/knowledge/categories/{id}/remove', 'HelpDeskCategoriesController@remove');
    Route::post('/helpdesk/knowledge/pages', 'HelpDeskPagesController@storePage');
    Route::post('/helpdesk/pages/{id}/remove', 'HelpDeskPagesController@removePage');
    Route::post('/helpdesk/pages/{id}/update', 'HelpDeskPagesController@updatePage');
    Route::post('/helpdesk/pages/{id}/feedback', 'HelpDeskFeedbackController@store');
    Route::post('/helpdesk/pages/{id}/sendFeedback', 'HelpDeskFeedbackController@sendFeedback');
    Route::post('/helpdesk/contents', 'PageContentUploadController@upload');

    Route::post('/images', 'PageContentUploadController@upload');

    Route::get('/certificates/{type}/{certificate_id?}', 'CertificatesController@getCertificate');
    Route::post('/certificates/{type}', 'CertificatesController@store');
    Route::post('/certificates/{type}/{certificate_id}', 'CertificatesController@update');

    Route::get('/tests/{test_id}/results', 'TestsController@results');
    Route::post('/tests/{test_id}/reminders', 'TestsController@storeReminder');
    Route::get('/tests/{test_id}/reminders', 'TestsController@getReminders');
    Route::post('/tests/{test_id}/remind', 'TestsController@sendReminder');
    Route::post('/tests/{test_id}/update', 'TestsController@updateTest');
    Route::post('/tests/{test_id}/cover', 'TestsController@cover');
    Route::post('/tests/{test_id}/icon', 'TestsController@icon');
    Route::post('/tests/{test_id}/questions', 'TestsController@updateQuestions');
    Route::post('/tests/{test_id}/categories', 'TestsController@updateCategories');
    Route::delete('/tests/{test_id}/delete', 'TestsController@delete');
    Route::get('/tests/{test_id}/delete-information', 'TestsController@deleteInformation');
    Route::post('/tests/{test_id}/archive', 'TestsController@archive');
    Route::post('/tests/{test_id}/unarchive', 'TestsController@unarchive');
    Route::get('/tests/{test_id}', 'TestsController@show');
    Route::get('/tests', 'TestsController@index');
    Route::post('/tests', 'TestsController@store');

    Route::get('/jobs', 'JobsController@getRunningJobs');

    Route::get('/vouchers', 'VouchersController@index');
    Route::post('/vouchers', 'VouchersController@store');
    Route::get('/vouchers/{id}', 'VouchersController@show');
    Route::post('/vouchers/{id}', 'VouchersController@update');
    Route::delete('/vouchers/{id}', 'VouchersController@delete');
    Route::post('/vouchers/{id}/produce', 'VouchersController@produce');
    Route::post('/vouchers/{id}/archive', 'VouchersController@archive');
    Route::post('/vouchers/{id}/unarchive', 'VouchersController@unarchive');
    Route::get('/vouchers/{id}/delete-information', 'VouchersController@deleteInformation');

    Route::get('/tag-groups', 'TagGroupsController@findTaggroups');
    Route::get('/tag-groups/get-tag-groups', 'TagGroupsController@getTagGroups');
    Route::post('/tag-groups', 'TagGroupsController@store');
    Route::post('/tag-groups/{id}', 'TagGroupsController@update');
    Route::post('/tag-groups/{id}/remove', 'TagGroupsController@remove');

    Route::get('/question-categories', 'CategoriesController@getActiveQuestionCategories');

    Route::get('/categories', 'CategoriesController@getCategories');
    Route::post('/categories', 'CategoriesController@createCategory');
    Route::post('/categories/{id}', 'CategoriesController@updateCategory');
    Route::get('/categories/{id}/delete-information', 'CategoriesController@deleteInformation');
    Route::delete('/categories/{id}', 'CategoriesController@deleteCategory');

    Route::post('/categories/search', 'CategoriesController@search');

    Route::post('/categories/groups', 'CategoriesController@createCategoryGroup');
    Route::post('/categories/groups/{id}', 'CategoriesController@updateCategoryGroup');
    Route::post('/categories/groups/{id}/delete', 'CategoriesController@deleteCategoryGroup');

    Route::get('/tags', 'TagsController@index');
    Route::get('/tags/get-tags', 'TagsController@getTags');
    Route::get('/tags/{tagId}', 'TagsController@show');
    Route::post('/tags/{tagId}', 'TagsController@update');
    Route::delete('/tags/{tagId}', 'TagsController@delete');
    Route::get('/tags/{tagId}/delete-information', 'TagsController@deleteInformation');
    Route::post('/tags', 'TagsController@store');

    Route::get('/wbt/events', 'WbtController@getEvents');

    Route::get('/imports', 'ImportsController@getLastImports');
    Route::get('/imports/{id}', 'ImportsController@getImport');
    Route::get('/import/configuration/userimport', 'UsersImportController@userImportConfiguration');
    Route::post('/import/collect-changes/userimport', 'UsersImportController@collectChanges');
    Route::post('/import/collect-changes/userdeletion', 'UsersDeletionController@collectChanges');
    Route::post('/import/users', 'UsersImportController@import');
    Route::post('/import/users-deletion', 'UsersDeletionController@import');
    Route::post('/import/questions', 'QuestionsImportController@import');
    Route::post('/import/indexcards', 'IndexcardsImportController@import');

    Route::get('/settings/app', 'SettingsController@getAppSettings');
    Route::get('/settings/translations/{appProfileId}', 'SettingsController@getAppTranslations');
    Route::get('/settings/appConfigItems', 'SettingsController@getAppConfig');
    Route::get('/settings/dummyUser', 'SettingsController@getDummyUser');
    Route::get('/settings/profile/{profileId}', 'SettingsController@getAppProfileSettings');
    Route::get('/settings/profile/{profileId}/homeComponents', 'SettingsController@getHomeComponents');
    Route::get('/settings/profiles', 'SettingsController@getAppProfiles');
    Route::get('/settings/customerInfo', 'SettingsController@getCustomerInfo');
    Route::get('/settings/templateInheritances', 'SettingsController@getTemplateInheritances');
    Route::get('/settings/availableModules', 'SettingsController@getAvailableModules');
    Route::get('/settings/isCandy', 'SettingsController@isCandy');

    Route::post('/settings/app', 'SettingsController@updateAppSettings');
    Route::post('/settings/translations/{appProfileId}', 'SettingsController@updateAppTranslations');
    Route::post('/settings/appConfigItems', 'SettingsController@updateAppConfigItems');
    Route::post('/settings/customerInfo', 'SettingsController@updateCustomerInfo');
    Route::post('/settings/templateInheritances', 'SettingsController@updateTemplateInheritances');
    Route::post('/settings/profile/{profileId}', 'SettingsController@updateAppProfileSettings');
    Route::post('/settings/profile/{profileId}/homeComponents', 'SettingsController@updateHomeComponents');
    Route::post('/settings/profile/{profileId}/image/{setting}', 'SettingsController@updateAppProfileImage');
    Route::post('/settings/dummyUser', 'SettingsController@updateDummyUser');
    Route::post('/settings/test-smtp', 'SettingsController@testSmtpSettings');

    Route::get('/ratings', 'RatingsController@getRatings');

    Route::get('/users', 'UsersController@index');
    Route::post('/users', 'UsersController@storeMultiple');
    Route::get('/users/admins', 'UsersController@getAdmins');
    Route::post('/users/delete', 'UsersController@removeUsers');
    Route::post('/users/reinvite', 'UsersController@reinviteUsers');
    Route::post('/users/deletion-information', 'UsersController@getDeletionInformation');
    Route::post('/users/tags', 'UsersController@addTags');
    Route::post('/users/delete-tags', 'UsersController@deleteTags');
    Route::post('/users/expiration', 'UsersController@setExpiration');
    Route::get('/users/warnings', 'UsersController@getWarnings');
    Route::get('/users/{userId}', 'UsersController@show');
    Route::post('/users/{userId}', 'UsersController@update');
    Route::post('/users/{userId}/resetPassword', 'UsersController@resetPassword');
    Route::post('/users/{userId}/restore', 'UsersController@restore');
    Route::post('/users/{userId}/send-message', 'DirectMessagesController@store');
    Route::get('/users/{userId}/direct-messages', 'DirectMessagesController@index');
    Route::delete('/users/{id}', 'UsersController@delete');
    Route::get('/users/{id}/delete-information', 'UsersController@deleteInformation');

    Route::get('/user-roles', 'UserRolesController@index');
    Route::get('/user-roles/{id}', 'UserRolesController@show');
    Route::post('/user-roles', 'UserRolesController@create');
    Route::post('/user-roles/{id}', 'UserRolesController@update');
    Route::delete('/user-roles/{id}', 'UserRolesController@delete');
    Route::post('/user-roles/{id}/clone', 'UserRolesController@clone');
    Route::get('/user-roles/{id}/delete-information', 'UserRolesController@deleteInformation');

    Route::get('/webinars', 'WebinarsController@getWebinars');
    Route::get('/webinars/{id}', 'WebinarsController@getWebinar');
    Route::get('/webinars/{id}/recordings', 'WebinarsController@getRecordings');
    Route::post('/webinars/{id}/recordings/delete', 'WebinarsController@deleteRecording');
    Route::post('/webinars', 'WebinarsController@createWebinar');
    Route::post('/webinars/{id}', 'WebinarsController@updateWebinar');
    Route::post('/webinars/{id}/delete', 'WebinarsController@deleteWebinar');

    Route::get('/webinars/get-join-link/{additional_user_id}', 'WebinarsController@getJoinLink');
    Route::post('/webinars/send-additional-user-invitation', 'WebinarsController@sendSingleInvitation');

    Route::post('/todolists/{todolist_id}/items', 'TodolistItemsController@store');
    Route::get('/todolists/{todolist_id}/items', 'TodolistItemsController@list');
    Route::post('/todolists/{todolist_id}/update-items', 'TodolistItemsController@update');
    Route::delete('/todolists/{todolist_id}/items/{item_id}', 'TodolistItemsController@delete');
    Route::get('/todolists/{todolist_id}/items/{item_id}/delete-information', 'TodolistItemsController@deleteInformation');

    Route::get('/courses', 'CoursesController@index');
    Route::post('/courses', 'CoursesController@create');
    Route::get('/courses/templates', 'CoursesController@getAllTemplates');
    Route::get('/courses/{course_id}', 'CoursesController@show');
    Route::post('/courses/{course_id}', 'CoursesController@update');
    Route::post('/courses/{course_id}/clone', 'CoursesController@clone');
    Route::post('/courses/{course_id}/clone-as-template', 'CoursesController@cloneAsTemplate');
    Route::post('/courses/{course_id}/archive', 'CoursesController@archive');
    Route::post('/courses/{course_id}/unarchive', 'CoursesController@unarchive');
    Route::delete('/courses/{course_id}', 'CoursesController@delete');
    Route::get('/courses/{course_id}/delete-information', 'CoursesController@deleteInformation');
    Route::get('/courses/{course_id}/users-to-notify', 'CoursesController@usersToNotify');
    Route::post('/courses/{course_id}/cover', 'CoursesController@cover');
    Route::get('/courses/{course_id}/reminders', 'CourseRemindersController@index');
    Route::post('/courses/{course_id}/reminders', 'CourseRemindersController@store');
    Route::delete('/courses/{course_id}/reminders/{reminder_id}', 'CourseRemindersController@delete');
    Route::post('/courses/{course_id}/chapterpositions', 'CourseContentsController@updateChapterPositions');
    Route::post('/courses/{course_id}/contentpositions', 'CourseContentsController@updateContentPositions');
    Route::post('/courses/{course_id}/content', 'CourseContentsController@create');
    Route::get('/courses/{course_id}/content/{content_id}', 'CourseContentsController@show');
    Route::post('/courses/{course_id}/content/{content_id}', 'CourseContentsController@update');
    Route::delete('/courses/{course_id}/content/{content_id}', 'CourseContentsController@delete');
    Route::get('/courses/{course_id}/content/{content_id}/delete-information', 'CourseContentsController@deleteInformation');
    Route::post('/courses/{course_id}/chapter/{chapter_id}', 'CourseContentsController@updateChapter');
    Route::delete('/courses/{course_id}/chapter/{chapter_id}', 'CourseContentsController@deleteChapter');
    Route::get('/courses/{course_id}/chapter/{chapter_id}/delete-information', 'CourseContentsController@chapterDeleteInformation');

    Route::get('/courses/{course_id}/participations/{participation_id}/certificate/{attempt_id}', 'CourseParticipationsController@certificate')->name('courseCertificateDownloadInBackend');
    Route::post('/courses/{course_id}/participations/{participation_id}/mark-as-not-finished', 'CourseParticipationsController@markAsNotFinished');
    Route::get('/courses/{course_id}/participations/participants', 'CourseParticipationsController@getParticipants');
    Route::get('/courses/{course_id}/participations/{participation_id}/todolist-status/{content_id}', 'CourseParticipationsController@getTodolistStatus');

    Route::get('/course-statistics/{course_id}/users', 'CourseStatisticsController@users');
    Route::get('/course-statistics/{courseId}/forms/{courseContentId}', 'CourseStatisticsController@formAnswers');
    Route::get('/course-statistics/{course_id}/overall', 'CourseStatisticsController@courseProgress');

    Route::get('/course-contents/forms/{formId}', 'CourseContentsController@getFormCourseContents');
    /* Template inheritance */
    Route::get('/template-inheritance/get-child-apps', 'TemplateInheritanceController@getChildApps');

    Route::get('/azure-video/{id}', 'AzureVideoController@status');

    Route::get('/search/tags-user-count/{tag_ids?}', 'SearchController@tagsUserCount');
    Route::get('/search/users/{module}/from-ids', 'SearchController@usersFromIds');
    Route::get('/search/users/{module}', 'SearchController@users');

    Route::get('/learningmaterials', 'LearningMaterialsController@index');
    Route::get('/learningmaterials/{id}', 'LearningMaterialsController@show');
    Route::post('/learningmaterials', 'LearningMaterialsController@store');
    Route::post('/learningmaterials/{id}', 'LearningMaterialsController@update');
    Route::post('/learningmaterials/{id}/clone', 'LearningMaterialsController@clone');
    Route::post('/learningmaterials/{id}/notify', 'LearningMaterialsController@notify');
    Route::post('/learningmaterials/{id}/cover', 'LearningMaterialsController@uploadCoverImage');
    Route::post('/learningmaterials/{id}/upload', 'LearningMaterialsController@upload');
    Route::post('/learningmaterials/{id}/reset', 'LearningMaterialsController@reset');
    Route::delete('/learningmaterials/{id}', 'LearningMaterialsController@delete');
    Route::get('/learningmaterials/{id}/delete-information', 'LearningMaterialsController@deleteInformation');

    Route::post('/learningmaterialfolders/{id}', 'LearningMaterialFoldersController@update');
    Route::post('/learningmaterialfolders', 'LearningMaterialFoldersController@store');
    Route::delete('/learningmaterialfolders/{id}', 'LearningMaterialFoldersController@delete');
    Route::get('/learningmaterialfolders/{id}/delete-information', 'LearningMaterialFoldersController@deleteInformation');

    Route::get('/advertisements', 'AdvertisementsController@index');
    Route::post('/advertisements', 'AdvertisementsController@store');
    Route::get('/advertisements/{id}', 'AdvertisementsController@show');
    Route::post('/advertisements/{id}', 'AdvertisementsController@update');
    Route::post('/advertisements/assets/{type}/{id}', 'AdvertisementsController@uploadAsset');
    Route::delete('/advertisements/{id}', 'AdvertisementsController@delete');
    Route::get('/advertisements/{id}/delete-information', 'AdvertisementsController@deleteInformation');

    Route::get('/app/configuration', 'AppController@getConfiguration');

    Route::get('/content-categories', 'ContentCategoriesController@index');
    Route::post('/content-categories', 'ContentCategoriesController@store');
    Route::get('/content-categories/{id}', 'ContentCategoriesController@show');
    Route::post('/content-categories/{id}', 'ContentCategoriesController@update');
    Route::delete('/content-categories/{id}', 'ContentCategoriesController@delete');
    Route::get('/content-categories/{id}/delete-information', 'ContentCategoriesController@deleteInformation');

    Route::get('/news', 'NewsController@index');
    Route::post('/news', 'NewsController@store');
    Route::get('/news/{id}', 'NewsController@show');
    Route::post('/news/{id}', 'NewsController@update');
    Route::delete('/news/{id}', 'NewsController@delete');
    Route::post('/news/{id}/notify', 'NewsController@notify');
    Route::post('/news/{id}/cover', 'NewsController@uploadCoverImage');
    Route::get('/news/{id}/delete-information', 'NewsController@deleteInformation');

    Route::get('/keywords', 'KeywordsController@index');
    Route::post('/keywords', 'KeywordsController@store');
    Route::get('/keywords/{id}', 'KeywordsController@show');
    Route::post('/keywords/{id}', 'KeywordsController@update');
    Route::delete('/keywords/{id}', 'KeywordsController@delete');
    Route::get('/keywords/{id}/delete-information', 'KeywordsController@deleteInformation');

    Route::get('/stats/users', 'StatsUsersController@index');

    Route::get('/pages', 'PagesController@index');
    Route::post('/pages', 'PagesController@store');
    Route::get('/pages/{id}', 'PagesController@show');
    Route::post('/pages/{id}', 'PagesController@update');
    Route::delete('/pages/{id}', 'PagesController@delete');
    Route::get('/pages/{id}/delete-information', 'PagesController@deleteInformation');
    Route::get('/pages/main-pages', 'PagesController@mainPages');
    Route::get('/pages/sub-pages', 'PagesController@subPages');

    Route::get('/comments', 'CommentsController@index');
    Route::get('/comments/unresolved', 'CommentsController@unresolvedCommentsCount');
    Route::get('/comments/{foreignType}/{foreignId}', 'CommentsController@commentsForEntry');
    Route::post('/comments/{id}', 'CommentsController@delete');
    Route::post('/comments/{id}/mark-as-harmless', 'CommentsController@markAsHarmless');
    Route::post('/comments/{id}/reply', 'CommentsController@reply');

    Route::get('/suggested-questions', 'SuggestedQuestionsController@index');
    Route::get('/suggested-questions/{id}', 'SuggestedQuestionsController@show');
    Route::get('/suggested-questions/{id}/accept', 'SuggestedQuestionsController@accept');
    Route::delete('/suggested-questions/{id}', 'SuggestedQuestionsController@delete');
    Route::get('/suggested-questions/{id}/delete-information', 'SuggestedQuestionsController@deleteInformation');

    Route::get('/reportings', 'ReportingsController@index');
    Route::post('/reportings', 'ReportingsController@store');
    Route::get('/reportings/{id}', 'ReportingsController@show');
    Route::post('/reportings/{id}', 'ReportingsController@update');
    Route::delete('/reportings/{id}', 'ReportingsController@delete');
    Route::get('/reportings/{id}/delete-information', 'ReportingsController@deleteInformation');

    Route::get('/stats/quiz/players', 'StatsQuizController@players');
    Route::get('/stats/quiz/questions', 'StatsQuizController@questions');
    Route::get('/stats/quiz/categories', 'StatsQuizController@categories');
    Route::get('/stats/quiz/quiz-teams', 'StatsQuizController@quizTeams');

    Route::get('/apps/details', 'AppController@getDetailsOfAllApps');

    Route::get('/stats/dashboard', 'StatsController@dashboard');

    Route::get('/quiz-teams', 'QuizTeamsController@index');
    Route::get('/quiz-teams/list', 'QuizTeamsController@list');
    Route::get('/quiz-teams/validQuizTeamName', 'QuizTeamsController@validQuizTeamName');
    Route::post('/quiz-teams', 'QuizTeamsController@store');
    Route::get('/quiz-teams/{id}', 'QuizTeamsController@show');
    Route::post('/quiz-teams/{id}', 'QuizTeamsController@update');
    Route::delete('/quiz-teams/{id}', 'QuizTeamsController@delete');
    Route::get('/quiz-teams/{id}/delete-information', 'QuizTeamsController@deleteInformation');

    Route::get('/appointments', 'AppointmentsController@index');
    Route::get('/appointments/all', 'AppointmentsController@getAll');
    Route::post('/appointments', 'AppointmentsController@store');
    Route::get('/appointments/{appointmentId}', 'AppointmentsController@show');
    Route::post('/appointments/{appointmentId}/cover', 'AppointmentsController@cover');
    Route::post('/appointments/{appointmentId}/cancel', 'AppointmentsController@cancel');
    Route::post('/appointments/{appointmentId}/convert-to-draft', 'AppointmentsController@convertToDraft');
    Route::post('/appointments/{appointmentId}/notify', 'AppointmentsController@notify');
    Route::post('/appointments/{appointmentId}', 'AppointmentsController@update');
    Route::delete('/appointments/{appointmentId}', 'AppointmentsController@delete');
    Route::get('/appointments/{appointmentId}/delete-information', 'AppointmentsController@deleteInformation');

    Route::get('/forms', 'FormsController@index');
    Route::get('/forms/all', 'FormsController@getAll');
    Route::post('/forms', 'FormsController@store');
    Route::get('/forms/{formId}', 'FormsController@show');
    Route::post('/forms/{formId}/cover', 'FormsController@cover');
    Route::post('/forms/{formId}/convert-to-draft', 'FormsController@convertToDraft');
    Route::post('/forms/{formId}/archive', 'FormsController@archive');
    Route::post('/forms/{formId}/unarchive', 'FormsController@unarchive');
    Route::post('/forms/{formId}', 'FormsController@update');
    Route::delete('/forms/{formId}', 'FormsController@delete');
    Route::get('/forms/{formId}/delete-information', 'FormsController@deleteInformation');
    Route::post('/forms/{formId}/fields', 'FormFieldsController@store');
    Route::delete('/forms/{formId}/fields/{formFieldId}', 'FormFieldsController@delete');
    Route::get('/forms/{formId}/fields/{formFieldId}/delete-information', 'FormFieldsController@deleteInformation');
});
