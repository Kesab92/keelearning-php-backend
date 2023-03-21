<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Courses\CourseContent;
use App\Models\Forms\Form;
use App\Models\Forms\FormField;
use App\Services\Courses\CoursesEngine;
use App\Services\Courses\CourseStatisticsEngine;
use App\Traits\PersonalData;
use App\Transformers\BackendApi\CourseStatistics\FormAnswerTransformer;
use App\Transformers\BackendApi\CourseStatistics\FormFieldTransformer;
use Auth;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseStatisticsController extends Controller
{
    use PersonalData;

    const ORDER_BY = [
        'id',
        'username',
        'passed_attempts',
        'passed',
    ];
    const PER_PAGE = [
        15,
        50,
        100,
        200,
    ];
    /**
     * @var CoursesEngine
     */
    private CoursesEngine $coursesEngine;

    public function __construct(CoursesEngine $coursesEngine)
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:courses,courses-stats');
        $this->personalDataRightsMiddleware('courses');
        $this->coursesEngine = $coursesEngine;
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function users($courseId, Request $request, CourseStatisticsEngine $courseStatisticsEngine)
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());

        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY) && !Str::startsWith($orderBy, 'progress_')) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $search = $request->input('search');
        $tags = $request->input('tags', []);
        $filter = $request->input('filter', null);

        $userData = $courseStatisticsEngine->getUsersProgress($course, Auth::user(), $search, $tags, $filter, $orderBy, $orderDescending, $page, $perPage, $this->showPersonalData, $this->showEmails);

        return response()->json($userData);
    }

    public function courseProgress($courseId, CourseStatisticsEngine $courseStatisticsEngine)
    {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());
        $courseProgress = $courseStatisticsEngine->getCourseProgress($course, Auth::user());
        return response()->json($courseProgress);
    }

    /**
     * Returns answers for the form
     * @param int $courseId
     * @param int $courseContentId
     * @param Request $request
     * @param CourseStatisticsEngine $courseStatisticsEngine
     * @param FormFieldTransformer $formFieldTransformer
     * @return JsonResponse
     */
    public function formAnswers(int $courseId, int $courseContentId, Request $request, CourseStatisticsEngine $courseStatisticsEngine, FormFieldTransformer $formFieldTransformer) {
        $course = $this->coursesEngine->getCourse($courseId, Auth::user());
        $courseContent = CourseContent
            ::where('type', CourseContent::TYPE_FORM)
            ->where('id', $courseContentId)
            ->first();

        if(!$courseContent) {
            abort(404);
        }

        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY) && !Str::startsWith($orderBy, 'progress_')) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $search = $request->input('search');
        $tags = $request->input('tags', []);

        $form = Form
            ::where('app_id', appId())
            ->with('fields.translationRelation')
            ->findOrFail($courseContent->foreign_id);
        $formAnswers = $courseStatisticsEngine->getFormAnswers($course, $courseContent, Auth::user(), $search, $tags, $orderBy, $orderDescending, $page, $perPage, $this->showPersonalData, $this->showEmails);

        $formAnswerTransformer = app(FormAnswerTransformer::class, [
            'showPersonalData' => $this->showPersonalData,
            'showEmails' => $this->showEmails,
        ]);

        $fields = $form->fields
            ->whereNotIn('type', FormField::READONLY_TYPES)
            ->sortBy('position')
            ->values();

        return response()->json([
            'count' => $formAnswers['count'],
            'fields' => $formFieldTransformer->transformAll($fields),
            'answers' => $formAnswerTransformer->transformAll($formAnswers['formAnswers']),
            ]);
    }
}
