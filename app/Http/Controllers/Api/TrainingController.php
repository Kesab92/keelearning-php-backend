<?php

namespace App\Http\Controllers\Api;

use App\Events\GameChatMessage;
use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryHider;
use App\Models\Game;
use App\Models\Page;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\TrainingAnswer;
use App\Models\User;
use App\Services\DoorKeeper;
use App\Services\StatsEngine;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis as Redis;
use Illuminate\Support\Facades\Request as Input;
use Response;

class TrainingController extends Controller
{
    /**
     * Gets all categories the user can play.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categories()
    {
        $categories = user()->getQuestionCategories(CategoryHider::SCOPE_TRAINING);

        return Response::json($categories->toArray());
    }

    public function getQuestions($category_id)
    {
        /** @var Category $category */
        $category = Category::findOrFail($category_id);
        if ($category->app_id != user()->app_id) {
            app()->abort(403);
        }

        $questions = Question::with('questionAnswers', 'attachments')
                             ->where('type', '!=', Question::TYPE_INDEX_CARD)
                             ->where('visible', 1)
                             ->where('category_id', $category_id)
                             ->get()
                             ->toArray();
        shuffle($questions);

        $questions = array_map(function ($question) use ($category) {
            $question['category'] = $category->name;
            shuffle($question['question_answers']);

            return $question;
        }, $questions);

        return Response::json($questions);
    }

    public function saveAnswer($question_id)
    {
        /** @var Question $question */
        $question = Question::findOrFail($question_id);
        if ($question->app_id != user()->app_id) {
            app()->abort(403);
        }

        $answer_ids = Input::get('answer_ids');

        $isCorrect = $question->isCorrect($answer_ids);

        $trainingAnswer = new TrainingAnswer();
        $trainingAnswer->user_id = user()->id;
        $trainingAnswer->question_id = $question->id;
        $trainingAnswer->answer_ids = $answer_ids;
        $trainingAnswer->correct = $isCorrect;
        $trainingAnswer->save();

        if ($question->type == Question::TYPE_MULTIPLE_CHOICE) {
            $correctAnswers = $question->questionAnswers()
                                       ->where('correct', 1)
                                       ->pluck('question_answers.id');
            $correctAnswers = $correctAnswers->map(function ($answer) {
                return (int) $answer;
            });

            return Response::json([
                    'correct_answer_id' => $correctAnswers,
                    'feedback'          => QuestionAnswer::whereIn('id', $answer_ids)->get()->pluck('feedback', 'id'),
                    'result'            => $isCorrect,
            ]);
        } else {
            $feedback = QuestionAnswer::where('id', $answer_ids)->get()->pluck('feedback', 'id');
            // Look for the correct result
            foreach ($question->questionAnswers as $otherQuestionAnswer) {
                // Return the json with the id of the correct answer
                if ($otherQuestionAnswer->correct) {
                    if (! $feedback[$answer_ids]) {
                        $feedback = [$otherQuestionAnswer->id => $otherQuestionAnswer->feedback];
                    }
                    $response = [
                            'correct_answer_id' => $otherQuestionAnswer->id,
                            'feedback'          => $feedback,
                            'result'            => $isCorrect,
                    ];

                    return Response::json($response);
                }
            }
        }
    }

    public function stats()
    {
        $stats = new StatsEngine(user()->app_id);

        return Response::json($stats->getPlayerTraining(user()));
    }
}
