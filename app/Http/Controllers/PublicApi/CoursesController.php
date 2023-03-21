<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\Course\CourseListFormRequest;
use App\Http\Requests\PublicApi\Course\CourseStatisticsListFormRequest;
use App\Models\Courses\Course;
use App\Models\Courses\CourseParticipation;
use App\Transformers\PublicApi\CourseStatisticsTransformer;
use App\Transformers\PublicApi\CourseTemplateTransformer;
use App\Transformers\PublicApi\CourseTransformer;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class CoursesController extends Controller
{
    /**
     * Returns a list of courses.
     *
     * @param CourseListFormRequest $request
     * @param CourseTransformer $courseTransformer
     * @return JsonResponse
     */
    public function index(CourseListFormRequest $request, CourseTransformer $courseTransformer):JsonResponse {
        $appId = Auth::user()->app_id;

        $validated = $request->validated();

        $courses = Course::where('app_id', $appId)
            ->offset($validated['perPage'] * $validated['page'])
            ->limit($validated['perPage'])
            ->with([
                'translationRelation',
                'contents',
                ])
            ->get();

        return response()->json($courseTransformer->transformAll($courses));
    }

    /**
     * Returns a list of templates.
     *
     * @param CourseListFormRequest $request
     * @param CourseTemplateTransformer $courseTemplateTransformer
     * @return JsonResponse
     */
    public function templates(CourseListFormRequest $request, CourseTemplateTransformer $courseTemplateTransformer):JsonResponse {
        $appId = Auth::user()->app_id;

        $validated = $request->validated();

        $courses = Course::where('app_id', $appId)
            ->template()
            ->offset($validated['perPage'] * $validated['page'])
            ->limit($validated['perPage'])
            ->with([
                'translationRelation',
                'contents',
            ])
            ->get();

        return response()->json($courseTemplateTransformer->transformAll($courses));
    }

    /**
     * Returns statistics of a course.
     *
     * @param int $courseId
     * @param CourseStatisticsListFormRequest $request
     * @param CourseStatisticsTransformer $courseStatisticsTransformer
     * @return JsonResponse
     */
    public function statistics(int $courseId, CourseStatisticsListFormRequest $request, CourseStatisticsTransformer $courseStatisticsTransformer):JsonResponse {
        $validated = $request->validated();

        $courseParticipations = CourseParticipation::where('course_id', $courseId)
            ->offset($validated['perPage'] * $validated['page'])
            ->limit($validated['perPage'])
            ->with([
                'contentAttempts',
                'user',
                'course.contents'
            ])
            ->when(isset($validated['orderBy']), function(Builder $query) use ($validated) {
                switch ($validated['orderBy']) {
                    case 'started_at_asc':
                        $query->orderBy('course_participations.created_at');
                        break;
                    case 'started_at_desc':
                        $query->orderByDesc('course_participations.created_at');
                        break;
                    case 'updated_at_asc':
                        $query->orderBy('course_participations.updated_at');
                        break;
                    case 'updated_at_desc':
                        $query->orderByDesc('course_participations.updated_at');
                        break;
                }
            })
            ->get();

        return response()->json($courseStatisticsTransformer->transformAll($courseParticipations));
    }
}
