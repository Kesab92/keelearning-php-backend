<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\SuggestedQuestion;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionCreate;
use App\Services\SuggestedQuestionEngine;
use App\Traits\PersonalData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SuggestedQuestionsController extends Controller
{
    use PersonalData;
    const ORDER_BY = [
        'id',
        'created_at',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:suggested_questions,suggestedquestions-edit');
        $this->personalDataRightsMiddleware('suggestedquestions');
    }

    /**
     * Returns suggested questions data
     *
     * @param Request $request
     * @param SuggestedQuestionEngine $suggestedQuestionEngine
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, SuggestedQuestionEngine $suggestedQuestionEngine)
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

        $suggestedQuestionsQuery = $suggestedQuestionEngine->suggestedQuestionsFilterQuery(appId(), $orderBy, $orderDescending);

        if ($this->showPersonalData) {
            $suggestedQuestionsQuery->with('user');
        }

        $countSuggestedQuestions = $suggestedQuestionsQuery->count();
        $suggestedQuestions = $suggestedQuestionsQuery
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        return response()->json([
            'count' => $countSuggestedQuestions,
            'suggestedQuestions' => $suggestedQuestions,
        ]);
    }

    /**
     * Returns the suggested question using JSON
     *
     * @param $suggestedQuestionId
     * @return JsonResponse
     * @throws \Exception
     */
    public function show($suggestedQuestionId) {
        $suggestedQuestion = $this->getSuggestedQuestion($suggestedQuestionId);
        if ($this->showPersonalData) {
            $suggestedQuestion->load('user');
        }
        $suggestedQuestion->load(['questionAnswers', 'category']);
        return Response::json($this->getSuggestedQuestionResponse($suggestedQuestion));
    }

    /**
     * Accepts a suggested question.
     *
     * @param $id
     *
     * @param AccessLogEngine $accessLogEngine
     * @return JsonResponse
     * @throws \Exception
     */
    public function accept($id, AccessLogEngine $accessLogEngine)
    {
        $suggestedQuestion = $this->getSuggestedQuestion($id);

        // Create the question
        $question = new Question();
        $question->setLanguage(defaultAppLanguage(appId()));
        $question->app_id = appId();
        $question->title = $suggestedQuestion->title;
        $question->category_id = $suggestedQuestion->category_id;
        $question->save();

        // Create the answers
        foreach ($suggestedQuestion->questionAnswers as $suggestedAnswer) {
            $questionAnswer = new QuestionAnswer();
            $questionAnswer->setLanguage(defaultAppLanguage(appId()));
            $questionAnswer->content = $suggestedAnswer->content;
            $questionAnswer->correct = $suggestedAnswer->correct;
            $question->questionAnswers()->save($questionAnswer);
        }

        $suggestedQuestion->safeRemove();

        $accessLogEngine->log(AccessLog::ACTION_QUESTION_CREATE, new AccessLogQuestionCreate($question));

        return Response::json(['question' => $question]);
    }

    /**
     * Returns dependencies and blockers
     *
     * @param $keywordId
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteInformation($id)
    {
        $suggestedQuestion = $this->getSuggestedQuestion($id);
        return Response::json([
            'dependencies' => $suggestedQuestion->safeRemoveDependees(),
            'blockers' => $suggestedQuestion->getBlockingDependees(),
        ]);
    }

    /**
     * Deletes the suggested question
     *
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete($id) {
        $suggestedQuestion = $this->getSuggestedQuestion($id);

        $result = $suggestedQuestion->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Returns the suggested question
     *
     * @param $id
     * @return SuggestedQuestion
     * @throws \Exception
     */
    private function getSuggestedQuestion($id) {
        $suggestedQuestion = SuggestedQuestion::findOrFail($id);

        // Check the access rights
        if ($suggestedQuestion->app_id != appId()) {
            app()->abort(403);
        }
        return $suggestedQuestion;
    }

    /**
     * Returns the suggested question for the response
     *
     * @param SuggestedQuestion $suggestedQuestion
     * @return SuggestedQuestion[]
     * @throws \Exception
     */
    private function getSuggestedQuestionResponse(SuggestedQuestion $suggestedQuestion) {
        $suggestedQuestion = $suggestedQuestion->toArray();

        return [
            'suggestedQuestion' => $suggestedQuestion,
        ];
    }
}
