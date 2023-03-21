<?php

Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    // users
    Route::get('/users', 'UsersController@index');
    Route::post('/users', 'UsersController@store');
    Route::put('/users/{resourceId}', 'UsersController@update');
    Route::delete('/users/{resourceId}', 'UsersController@delete');

    // tags
    Route::get('/tags', 'TagsController@index');
    Route::post('/tags', 'TagsController@store');

    // courses
    Route::get('/courses', 'CoursesController@index');
    Route::get('/course-templates', 'CoursesController@templates');
    Route::get('/courses/{resourceId}/statistics', 'CoursesController@statistics');
});
