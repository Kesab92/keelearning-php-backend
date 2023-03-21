<?php

namespace App\Imports;

use App\Models\AccessLog;
use App\Models\App;
use App\Models\Import;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionAnswerTranslation;
use App\Models\QuestionTranslation;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionCreate;
use DB;

class QuestionsSingleChoiceImporter extends Importer
{
    use QuestionAttachable;

    protected $necessaryHeaders = [
        'question',
        'correct_answer',
        'incorrect_answer1',
    ];

    private array $headers;

    /**
     * @param $additionalData
     * @param $headers
     * @param $questions
     */
    protected function importData($additionalData, $headers, $questions)
    {
        $this->headers = $headers;

        $category = $additionalData['category'];
        $creatorId = $additionalData['creatorId'];
        $this->import = Import::findOrFail($additionalData['importId']);
        /** @var AccessLogEngine $accessLogEngine */
        $accessLogEngine = app(AccessLogEngine::class);

        /** @var App $app */
        $app = $category->app;
        $idx = 0;
        $questionsCount = count($questions);

        DB::transaction(function () use ($questions, $category, $app, $questionsCount, &$idx, $accessLogEngine, $creatorId) {
            foreach ($questions as $questionData) {
                $question = new Question();
                $question->category_id = $category->id;
                $question->visible = true;
                $question->app_id = $category->app_id;
                $question->type = Question::TYPE_SINGLE_CHOICE;
                $question->save();
                $questionTranslation = new QuestionTranslation();
                $questionTranslation->question_id = $question->id;
                $questionTranslation->title = $this->getDataPoint($questionData, $this->headers, 'question');
                $questionTranslation->language = $app->getLanguage();
                $questionTranslation->save();

                $correctAnswer = new QuestionAnswer();
                $correctAnswer->question_id = $question->id;
                $correctAnswer->correct = true;
                $correctAnswer->save();
                $correctAnswerTranslation = new QuestionAnswerTranslation();
                $correctAnswerTranslation->question_answer_id = $correctAnswer->id;
                $correctAnswerTranslation->language = $app->getLanguage();
                $correctAnswerTranslation->content = $this->getDataPoint($questionData, $this->headers, 'correct_answer');
                if ($this->hasData($this->headers, 'feedback')) {
                    $correctAnswerTranslation->feedback = $this->getDataPoint($questionData, $this->headers, 'feedback');
                }
                $correctAnswerTranslation->save();

                for ($i = 1; $i <= 4; $i++) {
                    // Only the first wrong answer is necessary
                    if ($i > 1) {
                        $hasWrongAnswer = false;
                        foreach ($this->headers as $idx => $headerEntry) {
                            if ($headerEntry === 'incorrect_answer'.$i) {
                                $hasWrongAnswer = true;
                            }
                        }
                        if (! $hasWrongAnswer) {
                            continue;
                        } else {
                            // Skip optional answers without content
                            if(!$this->getDataPoint($questionData, $this->headers, 'incorrect_answer'.$i)) {
                                continue;
                            }
                        }
                    }
                    $incorrectAnswer = new QuestionAnswer();
                    $incorrectAnswer->question_id = $question->id;
                    $incorrectAnswer->correct = false;
                    $incorrectAnswer->save();
                    $incorrectAnswerTranslation = new QuestionAnswerTranslation();
                    $incorrectAnswerTranslation->question_answer_id = $incorrectAnswer->id;
                    $incorrectAnswerTranslation->language = $app->getLanguage();
                    $incorrectAnswerTranslation->content = $this->getDataPoint($questionData, $this->headers, 'incorrect_answer'.$i);
                    $incorrectAnswerTranslation->save();
                }

                if ($this->hasData($this->headers, 'image')) {
                    $this->importAttachment($question->id, $questionData);
                }

                $accessLogEngine->log(AccessLog::ACTION_QUESTION_CREATE, new AccessLogQuestionCreate($question), $creatorId);

                $this->setStepProgress($idx++ / $questionsCount);
            }
            $this->stepDone();
        });

        $this->importDone();
    }
}
