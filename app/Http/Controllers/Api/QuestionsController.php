<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\SuggestedQuestion;
use App\Models\SuggestedQuestionAnswer;
use App\Models\UserQuestionData;
use App\Services\Access\QuestionAccess;
use App\Services\QuestionsEngine;
use App\Services\StatsEngine;
use App\Services\TranslationEngine;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;
use Validator;

class QuestionsController extends Controller
{
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    public function suggestionSettings()
    {
        $app = App::find(user()->app_id);

        $categories = user()->getQuestionCategories(null, false)->pluck('name', 'id');

        return Response::json([
            'answersPerQuestion' => $app->answers_per_question,
            'categories' => $categories,
        ]);
    }

    /**
     * The function saves the suggested question and answers after validating them.
     *
     * @param Request $request
     * @return APIError|\Illuminate\Http\JsonResponse
     */
    public function suggestQuestion(Request $request)
    {
        /** @var App $app */
        $app = user()->app;
        $answersCount = $app->answers_per_question;

        // Check if each given input is not empty
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category' => 'required',
            'correct' => 'required',
            'wrong.*' => 'required',
        ]);

        if ($validator->fails()) {
            return new APIError(__('errors.check_input'));
        }

        // Create the suggested question and answers out of the POST data
        $title = $request->get('title');
        $categoryId = $request->get('category');
        $correctString = $request->get('correct');
        $wrongArray = $request->get('wrong');

        // Also validate the count of answers. as we have a correct answer, we just need n-1 answers here
        if (count($wrongArray) < 3) {
            return new APIError(__('errors.min_answers_required', ['minanswers' => $answersCount]));
        }

        $suggestedQuestion = new SuggestedQuestion();
        $suggestedQuestion->app_id = user()->app_id;
        $suggestedQuestion->title = $title;
        $suggestedQuestion->category_id = $categoryId;
        $suggestedQuestion->user_id = user()->id;
        $suggestedQuestion->save();

        // Create the correct answer
        $correctSuggestedAnswer = new SuggestedQuestionAnswer();
        $correctSuggestedAnswer->suggested_question_id = $suggestedQuestion->id;
        $correctSuggestedAnswer->content = $correctString;
        $correctSuggestedAnswer->correct = 1;
        $correctSuggestedAnswer->save();

        // Create the wrong answers
        foreach ($wrongArray as $wrong) {
            $wrongSuggestedQuestion = new SuggestedQuestionAnswer();
            $wrongSuggestedQuestion->suggested_question_id = $suggestedQuestion->id;
            $wrongSuggestedQuestion->content = $wrong;
            $wrongSuggestedQuestion->correct = 0;
            $wrongSuggestedQuestion->save();
        }

        $this->mailer->sendSuggestedQuestionNotification(user(), $suggestedQuestion);

        AnalyticsEvent::log(user(), AnalyticsEvent::TYPE_QUESTION_SUGGESTED, $suggestedQuestion);

        return Response::json(['success' => 1]);
    }

    /**
     * Previews a question.
     *
     * @param $id
     * @param QuestionsEngine $questionsEngine
     * @param QuestionAccess $questionAccess
     * @param TranslationEngine $translationEngine
     * @return JsonResponse
     * @throws Exception
     */
    public function preview($id, QuestionsEngine $questionsEngine, QuestionAccess $questionAccess, TranslationEngine $translationEngine)
    {
        $user = user();
        /** @var Question $question */
        $question = DB::table('questions')
            ->where('app_id', $user->app_id)
            ->where('id', $id)
            ->first();

        if (! $question) {
            return Response::json([
                'success' => false,
            ], 404);
        }

        if (! $user->is_admin) {
            if (! $question->visible || ! $questionAccess->hasAccess($user, $question)) {
                return Response::json([
                    'success' => false,
                ], 403);
            }
        }
        $questions = collect([$question]);

        $questions = $translationEngine->attachQuestionTranslations($questions, $user->app);
        $translationEngine->attachQuestionAttachments($questions);
        $translationEngine->attachQuestionAnswers($questions);

        $answers = collect($question->answers)
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'content' => $a->content,
                ];
            });

        $attachments = isset($question->attachments) ? $question->attachments : [];

        $questionsEngine->formatQuestionForFrontend($question, $question->app_id);
        $category = Category::where('app_id', $user->app_id)
            ->where('id', $question->category_id)
            ->first();

        // Answers are only available if the user is an admin or it's one of the challenging questions
        $addAnswerData = false;
        if(user()->is_admin) {
            $addAnswerData = true;
        } else {
            $challengingQuestions = (new StatsEngine(appId()))->getChallengingQuestions(user());
            $addAnswerData = $challengingQuestions->contains('id',$question->id);
        }

        if($addAnswerData) {
            /** @var Collection|QuestionAnswer[] $dbAnswers */
            $dbAnswers = QuestionAnswer
                ::whereIn('id', $answers->pluck('id'))
                ->with('translationRelation')
                ->get();
            $answers->transform(function($answer) use ($dbAnswers) {
                $dbAnswer = $dbAnswers->find($answer['id']);
                if($dbAnswer) {
                    $answer['correct'] = $dbAnswer->correct;
                    $answer['feedback'] = $dbAnswer->feedback;
                }
                return $answer;
            });
        }

        return Response::json([
            'success' => true,
            'data' => [
                'id' => $question->id,
                'type' => $question->type,
                'latex' => $question->latex,
                'category' => $category->name,
                'category_parent' => $category->categorygroup ? $category->categorygroup->name : null,
                'category_image' => formatAssetURL($category->image_url),
                'title' => $question->title,
                'answers' => $answers,
                'attachments' => $attachments,
                'answertime' => $question->answertime,
            ],
        ]);
    }

    /**
     * Gets the user-specific data for a question.
     *
     * @param int $id
     * @param QuestionAccess $questionAccess
     * @return JsonResponse
     * @throws Exception
     */
    public function getUserData(int $id): JsonResponse
    {
        $user = user();
        $question = Question::where('app_id', $user->app_id)
            ->find($id);

        if (!$question) {
            return Response::json([
                'success' => false,
            ], 403);
        }

        $userQuestionData = UserQuestionData::ofUser($user)->ofQuestion($question)->first();

        return Response::json([
            'notes' => $userQuestionData ? $userQuestionData->notes : '',
        ]);
    }

    /**
     * Stores the user-specific data for a question.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function storeUserData(int $id, Request $request): JsonResponse
    {
        $user = user();
        $question = Question::where('app_id', $user->app_id)
            ->find($id);

        if (!$question) {
            return Response::json([
                'success' => false,
            ], 403);
        }

        UserQuestionData::unguard();
        UserQuestionData::updateOrCreate([
            'app_id' => $user->app_id,
            'user_id' => $user->id,
            'question_id' => $question->id,
        ], [
            'notes' => $request->get('notes'),
        ]);
        UserQuestionData::reguard();

        return Response::json([ 'success' => true ]);
    }
}
