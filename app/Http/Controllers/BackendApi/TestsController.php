<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\Category;
use App\Models\EventHistory;
use App\Models\Question;
use App\Models\Reminder;
use App\Models\ReminderMetadata;
use App\Models\Test;
use App\Models\TestCategory;
use App\Models\TestQuestion;
use App\Models\User;
use App\Services\ImageUploader;
use App\Services\MorphTypes;
use App\Services\ReminderEngine;
use App\Services\TestEngine;
use App\Traits\PersonalData;
use Auth;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Throwable;
use URL;

class TestsController extends Controller
{
    use PersonalData;

    const ORDER_BY = [
        'id',
        'active_until',
        'mode',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:tests,tests-edit|tests-view');
        $this->middleware('auth.backendaccess:tests,tests-edit')->only([
            'archive',
            'cover',
            'delete',
            'deleteInformation',
            'icon',
            'sendReminder',
            'store',
            'storeReminder',
            'unarchive',
            'updateCategories',
            'updateTest',
        ]);
        $this->middleware('auth.backendaccess:tests,tests-stats')->only(['results']);
        $this->personalDataRightsMiddleware('tests');
    }

    /**
     * Returns the tests data.
     *
     * @param Request $request
     * @param TestEngine $testEngine
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, TestEngine $testEngine)
    {
        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $filter = $request->input('filter');
        $search = $request->input('search');
        $tags = $request->input('tags', []);

        $testQuery = $testEngine->testsFilterQuery(appId(), Auth::user(), $search, $tags, $filter, $orderBy, $orderDescending);

        $testsCount = $testQuery->count();
        /** @var Test[] $tests */
        $tests = $testQuery
            ->with(['tags', 'translationRelation', 'certificateTemplates'])
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        $testEngine->attachHasReminders($tests);

        foreach($tests as $test) {
            $test['certificateTemplateExists'] = $test->hasCertificateTemplate();
        }

        $tests = array_map(function ($test) {
            unset($test['translation_relation']);

            return $test;
        }, $tests->values()->toArray());

        return response()->json([
            'tests' => $tests,
            'count' => $testsCount,
        ]);

    }
    public function show($testId)
    {
        $test = $this->getTest($testId);

        $certificate = $test->certificateTemplates()->first();

        return Response::json([
            'test' => $test,
            'certificate' => $certificate,
        ]);
    }

    /**
     * Adds the test
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $test = DB::transaction(function() use ($request) {
            $test = new Test();
            $test->app_id = appId();
            $test->setLanguage(defaultAppLanguage(appId()));
            $test->name = $request->get('name');
            $test->mode = $request->get('mode') == Test::MODE_CATEGORIES ? Test::MODE_CATEGORIES : Test::MODE_QUESTIONS;
            $test->save();
            $test->syncTags($request->input('tags', []));
            return $test;
        });

        return response()->json([
            'test' => $test,
        ]);
    }


    public function results($testId, ReminderEngine $reminderEngine)
    {
        $settings = app(\App\Services\AppSettings::class);
        /** @var Test $test */
        $test = Test::tagRights()
            ->with([
                'submissions.testSubmissionAnswers.testQuestion',
                'submissions.test',
                'testQuestions',
            ])
            ->findOrFail($testId);
        if ($test->app_id !== appId() && ! isSuperAdmin()) {
            app()->abort(403);
        }

        if ($test->mode == Test::MODE_QUESTIONS) {
            $questionIds = $test->testQuestions->pluck('question_id');
        }
        if ($test->mode == Test::MODE_CATEGORIES) {
            $questionIds = DB::table('test_submissions')
                ->join('test_submission_answers', 'test_submissions.id', '=', 'test_submission_answers.test_submission_id')
                ->where('test_submissions.test_id', $test->id)
                ->groupBy('question_id')
                ->pluck('question_id');
        }

        $participants = $test->participantIds();
        $questions = Question::whereIn('id', $questionIds)
            ->select(['id'])
            ->get()
            ->transform(function ($question) {
                return [
                    'id' => $question->id,
                    'title' => $question->title,
                ];
            });

        $eventHistory = EventHistory::whereIn('user_id', $participants)
            ->where('foreign_id', $test->id)
            ->get();
        $users = User::ofApp($test->app_id)
            ->whereIn('users.id', $participants)
            ->tagRightsJoin()
            ->groupBy('users.id')
            ->get();

        $users->transform(function ($user) use ($reminderEngine, $eventHistory, $test, $settings) {
            $userSubmissions = $test->submissions
                    ->where('user_id', $user->id);
            $passed = $userSubmissions
                    ->filter(function ($item) {
                        return $item->result > 0;
                    })
                    ->count() > 0;

            $lastSubmissionDate = $userSubmissions
                    ->sortByDesc('created_at')
                    ->first();

            if ($lastSubmissionDate) {
                $lastSubmissionDate = $lastSubmissionDate->created_at->toDateTimeString();
            }

            $certificateLink = null;
            if ($this->showPersonalData && $passed && $test->hasCertificateTemplate()) {
                $certificateLink = URL::signedRoute('certificateDownload', [
                        'submission_id' => $userSubmissions->where('result', 1)->first()->id,
                    ]);
            }

            $history = $reminderEngine->createHistory($user, $test, $eventHistory);

            return [
                    'id' => $user->id,
                    'passed' => $passed,
                    'history' => $history,
                    'name' => $this->showPersonalData ? $user->username : '',
                    'firstname' => $this->showPersonalData ? $user->firstname : '',
                    'lastname' => $this->showPersonalData ? $user->lastname : '',
                    'email' => $this->showEmails ? $user->email : '',
                    'date' => $lastSubmissionDate,
                    'certificateLink' => $certificateLink,
                ];
        });

        return Response::json([
            'success' => true,
            'data' => [
                'test' => [
                   'id' => $test->id,
                   'name' => $test->name,
                ],
                'users' => $users,
                'questions' => $questions,
                'attempts' => $test->attempts,
            ],
        ]);
    }

    /**
     * Stores or updates reminder of an test by test_id.
     * @param $test_id
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     * @throws Throwable
     */
    public function storeReminder($test_id, Request $request)
    {
        $this->validate($request, [
            'reminders' => 'present',
        ]);

        $test = $this->getTest($test_id);

        $user = Auth::user();

        DB::transaction(function () use ($test, $test_id, $request, $user) {
            $isFullAdmin = $user->tagRightsRelation()->count() == 0;
            if ($isFullAdmin) {
                $existingReminders = Reminder::where('foreign_id', $test_id)
                    ->whereIn('type', Reminder::TYPES[MorphTypes::TYPE_TEST])
                    ->get();
            } else {
                $existingReminders = Reminder::where('user_id', $user->id)
                    ->where('foreign_id', $test_id)
                    ->whereIn('type', Reminder::TYPES[MorphTypes::TYPE_TEST])
                    ->get();
            }
            $deleteReminderIds = array_diff(
                $existingReminders->pluck('id')->all(),
                collect($request->input('reminders'))->pluck('id')->all()
            );

            Reminder::whereIn('id', $deleteReminderIds)
                ->whereIn('type', Reminder::TYPES[MorphTypes::TYPE_TEST])
                ->delete();
            ReminderMetadata::whereIn('reminder_id', $deleteReminderIds)
                ->delete();

            // Create or Update
            foreach ($request->input('reminders') as $data) {
                $reminder = Reminder::where('id', $data['id'])
                    ->where('foreign_id', $test_id)
                    ->whereIn('type', Reminder::TYPES[MorphTypes::TYPE_TEST])
                    ->where('app_id', appId());

                if (! $isFullAdmin) {
                    $reminder = $reminder->where('user_id', $user->id);
                }

                $reminder = $reminder->first();

                if (! $reminder) {
                    $reminder = new Reminder();
                    $reminder->user_id = $user->id;
                }

                $reminder->foreign_id = $test_id;
                $reminder->app_id = appId();
                $reminder->type = $data['type'];
                $reminder->days_offset = $data['days'];
                $reminder->save();

                if ($reminder->type === Reminder::TYPE_TEST_RESULTS && empty($data['email'])) {
                    app()->abort(500, __('errors.test_no_reminder_mail'));
                }

                if (! empty($data['email'])) {
                    $metadata = null;
                    if ($reminder->id) {
                        $metadata = $reminder->metadata()->where('key', 'email')->first();
                    }

                    if (! $metadata) {
                        $metadata = new ReminderMetadata();
                    }

                    $metadata->reminder_id = $reminder->id;
                    $metadata->key = 'email';
                    $metadata->value = $data['email'];
                    $metadata->save();
                }
            }
        });

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Returns reminders to given tests id (test_result_reminder & user_notifications).
     * @param $test_id
     * @return
     */
    public function getReminders($test_id)
    {
        $test = $this->getTest($test_id);
        $user = Auth::user();

        $isFullAdmin = $user->tagRightsRelation()->count() == 0;

        $reminders = Reminder::where('foreign_id', $test_id)
            ->with('user')
            ->whereIn('type', Reminder::TYPES[MorphTypes::TYPE_TEST]);
        if (! $isFullAdmin) {
            $reminders = $reminders->where('user_id', $user->id);
        }
        $reminders = $reminders->get()
            ->transform(function ($reminder) use ($test, $isFullAdmin) {
                return [
                    'id'        => $reminder->id,
                    'days'      => $reminder->days_offset,
                    'email'     => $reminder->metadata()->where('key', 'email')->value('value'),
                    'type'      => $reminder->type,
                    'user_name' => ($isFullAdmin && $reminder->user) ? $reminder->user->getDisplayNameBackend($this->showEmails) : null,
                ];
            });

        return Response::json([
            'success' => true,
            'data' => [
                'test' => [
                    'id'           => $test->id,
                    'active_until' => $test->active_until ? $test->active_until->toDateTimeString() : null,
                    'name'         => $test->name,
                ],
                'reminders' => $reminders,
            ],
        ]);
    }

    /**
     * Sends reminder to all given users.
     * @‘param $test_id
     * @param $test_id
     * @param Request $request
     * @param ReminderEngine $reminderEngine
     * @param Mailer $mailer
     * @return JsonResponse
     * @throws Exception
     */
    public function sendReminder($test_id, Request $request, ReminderEngine $reminderEngine)
    {
        $this->validate($request, [
            'user_ids' => 'required',
        ]);

        $test = $this->getTest($test_id);

        if ($test->attempts == 1) {
            app()->abort(500, __('errors.test_not_repeatable'));
        }

        $users = User::where('app_id', appId())
            ->where('active', 1)
            ->whereNull('deleted_at')
            ->whereIn('id', $request->input('user_ids'))
            ->get();

        $reminderEngine->sendTestNotifications($users, $test);

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Updates test settings.
     */
    public function updateTest($test_id, Request $request)
    {
        $test = $this->getTest($test_id);

        if ($request->has('award_tag_ids')) {
            $test->awardTags()->sync($request->input('award_tag_ids'));
        }

        if ($request->has('tag_ids')) {
            $test->quiz_team_id = null;
            $test->tags()->sync($request->input('tag_ids'));
        }

        $test->name = $request->get('name');
        $test->description = $request->get('description');
        $test->min_rate = min(100, max(0, intval($request->get('min_rate'))));
        $test->minutes = intval($request->get('minutes')) ?: null;
        $test->attempts = max(0, intval($request->get('attempts'))) ?: null;
        $test->no_download = (bool) $request->get('no_download');
        $test->send_certificate_to_admin = (bool) $request->get('send_certificate_to_admin');
        $test->repeatable_after_pass = (bool) $request->get('repeatable_after_pass');
        $test->active_until = $request->get('active_until') ? $request->get('active_until').' 23:59:59' : null;
        $test->cover_image_url = $request->get('cover_image_url');
        $test->icon_url = $request->get('icon_url');

        $test->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Updates categories for a certain test.
     */
    public function updateCategories($test_id, Request $request)
    {
        $test = $this->getTest($test_id);

        if ($test->mode != Test::MODE_CATEGORIES) {
            return Response::json([
                'error' => 'Diesem Test können keine Kategorien zugewiesen werden.',
                'success' => false,
            ]);
        }

        $categories = $request->get('categories');
        $categoryIds = collect($categories)->pluck('id');

        foreach ($categories as $categoryData) {
            $category = Category::find($categoryData['id']);
            if (! $testCategory = $test->testCategories->where('category_id', $categoryData['id'])->first()) {
                if (! $category || $category->app_id != appId()) {
                    continue;
                }

                $testCategory = new TestCategory;
                $testCategory->test_id = $test->id;
                $testCategory->category_id = $categoryData['id'];
            }
            $maxQuestionCount = $category
                ->questions()
                ->where('visible', 1)
                ->where('type', '!=', Question::TYPE_INDEX_CARD)
                ->count();
            $testCategory->question_amount = min(max(1, (int) $categoryData['question_amount']), $maxQuestionCount);
            $testCategory->save();
        }

        // remove unused testCategories
        TestCategory::where('test_id', $test->id)->whereNotIn('category_id', $categoryIds)->delete();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Updates questions for a certain test
     * we can not remove questions that have already been answered,
     * and need to retain the TestQuestions' IDs.
     */
    public function updateQuestions($test_id, Request $request)
    {
        $test = $this->getTest($test_id);

        if ($test->mode != Test::MODE_QUESTIONS) {
            return Response::json([
                'error' => 'Diesem Test können keine Fragen zugewiesen werden.',
                'success' => false,
            ]);
        }

        $questions = $request->get('questions');
        $questionIds = array_map(function ($question) {
            return $question['question_id'];
        }, $questions);

        // check if all required questions are still there
        $requiredTestQuestions = TestQuestion::where('test_id', $test->id)
            ->whereHas('answers')
            ->pluck('question_id')
            ->toArray();
        if (count(array_diff($requiredTestQuestions, $questionIds))) {
            return Response::json([
                'error' => 'Bereits beantwortete Fragen können nicht entfernt werden. Bitte laden Sie die Seite neu.',
                'success' => false,
            ]);
        }

        $index = 0;
        foreach ($questions as $questionData) {
            if (! $testQuestion = $test->testQuestions->where('question_id', $questionData['question_id'])->first()) {
                $question = Question::find($questionData['question_id']);
                if (! $question || $question->app_id != appId()) {
                    continue;
                }

                $testQuestion = new TestQuestion;
                $testQuestion->test_id = $test->id;
                $testQuestion->question_id = $questionData['question_id'];
            }
            $testQuestion->points = intval($questionData['points']) ?: null;
            $testQuestion->position = $index;
            $testQuestion->save();
            $index++;
        }

        // remove unused testQuestions
        TestQuestion::where('test_id', $test->id)->whereNotIn('question_id', $questionIds)->delete();

        return Response::json([
            'success' => true,
        ]);
    }

    public function cover($testId, Request $request, ImageUploader $imageUploader)
    {
        $test = $this->getTest($testId);

        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            app()->abort(403);
        }

        if (! $imagePath = $imageUploader->upload($file, 'uploads/test-cover')) {
            app()->abort(400);
        }
        $imagePath = formatAssetURL($imagePath, '3.0.0');

        return \Response::json([
            'image' => $imagePath,
        ]);
    }

    public function icon($testId, Request $request, ImageUploader $imageUploader)
    {
        $test = $this->getTest($testId);

        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            app()->abort(403);
        }

        if (! $imagePath = $imageUploader->upload($file, 'uploads/test-icon')) {
            app()->abort(400);
        }
        $imagePath = formatAssetURL($imagePath, '3.0.0');

        return \Response::json([
            'image' => $imagePath,
        ]);
    }

    /**
     * Returns dependencies and blockers
     *
     * @param $testId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteInformation($testId)
    {
        $test = $this->getTest($testId);
        return Response::json([
            'dependencies' => $test->safeRemoveDependees(),
            'blockers' => $test->getBlockingDependees(),
        ]);
    }

    /**
     * Deletes the test
     *
     * @param $testId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($testId) {
        $test = $this->getTest($testId);

        $result = $test->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Archives the test.
     * @param $testId
     * @return \Illuminate\Http\JsonResponse
     */
    public function archive($testId)
    {
        $test = $this->getTest($testId);

        $test->archived = true;
        $test->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Unarchives the test.
     * @param $testId
     * @return \Illuminate\Http\JsonResponse
     */
    public function unarchive($testId)
    {
        $test = $this->getTest($testId);

        $test->archived = false;
        $test->save();

        return Response::json([
            'success' => true,
        ]);
    }


    private function getTest($test_id)
    {
        $test = Test::tagRights()->find($test_id);

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
