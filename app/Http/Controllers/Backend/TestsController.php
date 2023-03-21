<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Exports\TestUserAnswers;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Category;
use App\Models\EventHistory;
use App\Models\QuizTeam;
use App\Models\Question;
use App\Models\Tag;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestSubmission;
use App\Models\User;
use App\Services\PermissionEngine;
use App\Services\ReminderEngine;
use App\Services\TestSubmissionRenderer;
use App\Traits\PersonalData;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Response;
use Session;
use Validator;
use View;

class TestsController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:tests,tests-edit|tests-view');
        $this->middleware('auth.backendaccess:tests,tests-edit')->only([
            'archive',
            'create',
            'getDeleteInformation',
            'remove',
        ]);
        $this->middleware('auth.backendaccess:tests,tests-stats')->only([
            'downloadUserAnswers',
            'results',
            'resultscsv',
            'resultsHistoryCSV',
        ]);
        $this->personalDataRightsMiddleware('tests');
        View::share('activeNav', 'tests');
    }

    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
            'props' => [
                'has_candy_frontend' => $this->appSettings->getValue('has_candy_frontend'),
            ],
        ]);
    }

    public function view($test_id)
    {
        $test = Test::tagRights()
            ->with('awardTags')
            ->with('tags')
            ->with('testCategories.category')
            ->with('testQuestions.question.category')
            ->where('app_id', appId())
            ->findOrFail($test_id);

        $test->append('url');

        $categoryMeta = Category::whereIn('categories.id', $test->testCategories->pluck('category_id'))
            ->select(
                DB::raw('categories.id as id'),
                DB::raw('COUNT(questions.id) as question_count'),
            )
            ->leftJoin('questions', 'questions.category_id', '=', 'categories.id')
            ->groupBy('categories.id')
            ->where('questions.visible', 1)
            ->where('questions.type', '!=', Question::TYPE_INDEX_CARD)
            ->get()
            ->keyBy('id');

        $test->testCategories->transform(function ($testCategory) use ($categoryMeta) {
            if (isset($categoryMeta[$testCategory->category_id])) {
                $testCategory->question_count = $categoryMeta[$testCategory->category_id]->question_count;
            } else {
                $testCategory->question_count = 0;
            }

            return $testCategory;
        });

        $testQuestionIds = $test->testQuestions->pluck('id');
        $testQuestionWithAnswers = TestQuestion::whereIn('test_questions.id', $testQuestionIds)
            ->join('test_submission_answers', 'test_submission_answers.test_question_id', '=', 'test_questions.id')
            ->groupBy('test_questions.id')
            ->pluck('test_questions.id')
            ->toArray();
        $test->testQuestions->transform(function ($testQuestion) use ($testQuestionWithAnswers) {
            $testQuestion->in_use = in_array($testQuestion->id, $testQuestionWithAnswers);

            return $testQuestion;
        });

        $tags = Tag::ofApp(appId())
            ->rights(Auth::user())
            ->with('tagGroup')
            ->get();

        return view('vue-component', [
            'component' => 'test-editor',
            'hasFluidContent' => false,
            'props' => [
                'quizTeams'  => QuizTeam::ofApp(appId())->get(),
                'tags'    => $tags,
                'test'    => $test,
            ],
        ]);
    }

    /**
     * Creates a new test.
     *
     * @param Request $request
     * @param PermissionEngine $permissionEngine
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    public function create(Request $request, PermissionEngine $permissionEngine)
    {
        $hasLimitedTAGAccess = Auth::user()->tagRightsRelation->pluck('id')->count() > 0;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'tag_ids' => $hasLimitedTAGAccess ? 'required' : [],
        ]);

        if ($validator->fails()) {
            Session::flash('error-message', 'Bitte füllen Sie alle Felder aus.');

            return redirect()->back();
        }
        try {
            $test = DB::transaction(function () use ($request, $permissionEngine) {
                $test = new Test();
                $test->app_id = appId();
                $test->name = $request->get('name');
                $test->mode = $request->get('mode') == Test::MODE_CATEGORIES ? Test::MODE_CATEGORIES : Test::MODE_QUESTIONS;
                $test->save();

                $permissionEngine->syncTags(
                    $test,
                    $request->input('tag_ids'),
                    Auth::user()->tagRightsRelation->pluck('id'));

                return $test;
            });

            return redirect()->to('/tests/'.$test->id);
        } catch (\Exception $e) {
            Session::flash('error-message', 'Der Test konnte nicht erstellt werden.');

            return redirect()->back();
        }
    }

    public function results($test_id)
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'test-results',
        ]);
    }

    public function resultscsv($test_id, PermissionEngine $permissionEngine)
    {
        $test = $this->getTest($test_id);

        $test->load('testQuestions.question.category');

        $userIds = User::where('app_id', appId())
            ->whereIn('users.id', $test->participantIds())
            ->tagRightsJoin()
            ->groupBy('users.id')
            ->pluck('users.id');
        $submissions = $test->submissions()
            ->whereIn('user_id', $userIds)
            ->with('user.tags', 'testSubmissionAnswers.testQuestion.question', 'testSubmissionAnswers.question.category', 'test.testQuestions.question.category')
            ->get();

        $tags = $permissionEngine->getAvailableTags($test->app_id, Auth::user());
        $tagGroups = $permissionEngine->getAvailableTagGroups($test->app_id, Auth::user());

        $data = [
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
            'submissions' => $submissions,
            'tags'        => $tags,
            'tagGroups'   => $tagGroups,
            'test'        => $test,
        ];

        return Excel::download(new DefaultExport($data, 'tests.resultscsv'), 'ergebnisse-'.Str::slug($test->name).'.xlsx');
    }

    /**
     * Shows the certificate designer.
     * @param $testId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function certificateDesigner($testId)
    {
        // Check access rights
        $this->getTest($testId);

        return view('vue-component', [
            'component' => 'test-certificate',
            'hasFluidContent' => false,
            'props' => [
                'test-id' => $testId,
            ],
        ]);
    }

    /**
     * @param $testId
     * @param $testSubmissionId
     * @throws \Exception
     */
    public function renderSubmissionPDF($testId, $testSubmissionId)
    {
        $testSubmission = TestSubmission::find($testSubmissionId);
        if ($testSubmission->test->app_id !== appId()) {
            app()->abort(403);
        }

        $testSubmissionRenderer = new TestSubmissionRenderer($testSubmission);

        $testSubmissionRenderer->render();
    }

    /**
     * @param $testId
     * @throws \Exception
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function certificatePreview($testId)
    {
        $test = $this->getTest($testId);

        $testSubmission = new TestSubmission();
        $testSubmission->id = 0;
        $testSubmission->result = 1;
        $testSubmission->user_id = Auth::user()->id;
        $testSubmission->test_id = $test->id;
        $testSubmission->updated_at = Carbon::now();

        $testSubmissionRenderer = new TestSubmissionRenderer($testSubmission, language());

        return $testSubmissionRenderer->render();
    }

    /**
     * Removes the test and TestQuestions.
     * @param $test_id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function remove($test_id)
    {
        $test = $this->getTest($test_id);

        $result = $test->safeRemove();
        if (! $result->success) {
            return Response::json([
                'success' => false,
            ]);
        }

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Archives the test.
     * @param $test_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function archive($test_id)
    {
        $test = $this->getTest($test_id);

        $test->archived = true;
        $test->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Returns test delete information, which dependencies are altered/deleted.
     * @param $test_id
     * @return JsonResponse
     */
    public function getDeleteInformation($test_id)
    {
        $test = $this->getTest($test_id);

        return Response::json([
           'success' => true,
           'data' => $test->safeRemoveDependees(),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reminders($test_id)
    {
        return view('vue-component', [
            'component' => 'reminders',
            'hasFluidContent' => false,
            'props' => [
                'test' => (int) $test_id,
            ],
        ]);
    }

    public function resultsHistoryCSV($test_id, ReminderEngine $reminderEngine, PermissionEngine $permissionEngine)
    {
        $test = $this->getTest($test_id);
        $test->load([
            'testQuestions.question.category',
            'submissions.testSubmissionAnswers.testQuestion.question',
            'submissions.testSubmissionAnswers.question.category',
            'submissions.test.testQuestions.question.category',
        ]);

        $userIds = User::where('app_id', appId())
            ->whereIn('users.id', $test->participantIds())
            ->tagRightsJoin()
            ->groupBy('users.id')
            ->pluck('users.id');

        $tags = $permissionEngine->getAvailableTags($test->app_id, Auth::user());
        $tagGroups = $permissionEngine->getAvailableTagGroups($test->app_id, Auth::user());

        $eventHistory = EventHistory::whereIn('user_id', $userIds)->get();
        $users = User::where('app_id', appId())
            ->whereIn('id', $userIds)
            ->with('tags')
            ->get()
            ->transform(function ($user) use ($eventHistory, $reminderEngine, $test) {
                $history = $reminderEngine->createHistory($user, $test, $eventHistory);

                return [
                    'email' => $user->email,
                    'username' => $user->username,
                    'history'  => $reminderEngine->transformHistory($history),
                    'tags' => $user->tags,
                ];
            });

        $data = [
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
            'tags' => $tags,
            'tagGroups' => $tagGroups,
            'users' => $users,
        ];

        return Excel::download(new DefaultExport($data, 'tests.results-history-csv'), 'ergebnis-verlauf-'.Str::slug($test->name).'.xlsx');
    }

    public function downloadUserAnswers($test_id, $user_id)
    {
        $test = $this->getTest($test_id);
        $user = User::tagRightsJoin()->findOrFail($user_id);
        if ($user->app_id != appId()) {
            app()->abort(403);
        }
        // get latest submission, preferring successful ones
        $submission = $test->submissions
            ->where('user_id', $user->id)
            ->sortByDesc('result')
            ->sortByDesc('created_at')
            ->first();

        if (!$submission) {
            app()->abort(404);
        }
        $test->load('testQuestions.question.questionAnswers');
        $submission->load('testSubmissionAnswers');
        return Excel::download(new TestUserAnswers($test, $user, $submission, $this->showPersonalData, $this->showEmails), 'test-'.Str::slug($test->name).'-antworten-'.$user->id.'.xlsx');
    }

    /**
     * Retrieves the test and check if it is from same app & the user has permission.
     * @param $id - ID of the test
     * @return Test test
     * @throws \Exception
     */
    private function getTest($id)
    {
        $test = Test::tagRights()->find($id);

        // Check if something was found
        if (! $test) {
            app()->abort(404);
        }

        // Check the access rights
        if ($test->app_id != appId()) {
            app()->abort(403);
        }

        return $test;
    }
}
