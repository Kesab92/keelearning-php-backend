<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Todolist;
use App\Models\TodolistItemAnswer;
use App\Services\Courses\CoursesEngine;
use App\Transformers\Api\Todolists\TodolistTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

class TodolistsController extends Controller
{
    /**
     * Gets all todolists available to the user
     *
     * @param CoursesEngine $coursesEngine
     * @return JsonResponse
     */
    public function getAllTodolists(CoursesEngine $coursesEngine): JsonResponse
    {
        $availableCourses = $coursesEngine->getUsersCourses(user());
        $userTags = user()->tags()->pluck('tags.id');

        $todolistContentAttemptIds = CourseContentAttempt
            ::join('course_contents', function ($join) {
                $join->on('course_contents.id', '=', 'course_content_attempts.course_content_id')
                    ->where('type', CourseContent::TYPE_TODOLIST)
                    ->where('visible', 1);
            })
            ->join('course_participations', function ($join) use ($availableCourses) {
                $join->on('course_participations.id', '=', 'course_content_attempts.course_participation_id')
                    ->where('user_id', user()->id)
                    ->whereIn('course_id', $availableCourses->pluck('id'));
            })
            ->leftJoin('course_content_tag', 'course_content_tag.course_content_id', '=', 'course_contents.id')
            ->where(function($query) use ($userTags) {
                // User has access via TAGs
                $query->whereNull('course_content_tag.tag_id')
                    ->orWhereIn('course_content_tag.tag_id', $userTags);
            })
            ->select('course_content_attempts.id');
        $todolistContentAttempts = CourseContentAttempt::whereIn('id', $todolistContentAttemptIds)
            ->with('content.course')
            ->get();
        $todolists = Todolist::with('todolistItems')
            ->whereIn('id', $todolistContentAttempts->pluck('content.foreign_id'))
            ->get()
            ->keyBy('id');
        $entries = [];
        $todolistTransformer = app(TodolistTransformer::class);
        foreach ($todolistContentAttempts as $todolistContentAttempt) {
            $entry = $todolistTransformer->transform($todolists->get($todolistContentAttempt['content']['foreign_id']));
            $entries[] = array_merge($entry, [
                'meta' => [
                    'attempt_id' => $todolistContentAttempt->id,
                    'content_id' => $todolistContentAttempt->content->id,
                    'course_id' => $todolistContentAttempt->content->course->id,
                    'finished' => $todolistContentAttempt->passed,
                    'image' => $todolistContentAttempt->content->course->cover_image_url,
                    'participation_id' => $todolistContentAttempt->course_participation_id,
                    'subtitle' => $todolistContentAttempt->content->course->title,
                    'title' => $todolistContentAttempt->content->title,
                ],
            ]);
        }
        return Response::json([
            'todolists' => $entries,
        ]);
    }

    /**
     * Gets all responses given for todolist items by the current user
     *
     * @return JsonResponse
     */
    public function getAllItemAnswers(): JsonResponse
    {
        return Response::json([
            'answers' => TodolistItemAnswer::where('user_id', user()->id)->pluck('is_done', 'todolist_item_id'),
        ]);
    }

    /**
     * Sets a given todolist item to done/not done
     *
     * @param int $itemId
     * @param Request $request
     * @return JsonResponse
     */
    public function setItemAnswer(int $itemId, Request $request): JsonResponse
    {
        $itemAnswer = TodolistItemAnswer::where('user_id', user()->id)
            ->where('todolist_item_id', $itemId)
            ->first();
        if (!$itemAnswer) {
            $itemAnswer = new TodolistItemAnswer;
            $itemAnswer->user_id = user()->id;
            $itemAnswer->todolist_item_id = $itemId;
        }
        $itemAnswer->is_done = !!$request->get('is_done');
        $itemAnswer->save();

        return Response::json([
            'itemAnswer' => $itemAnswer,
        ]);
    }
}
