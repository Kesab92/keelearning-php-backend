<?php

namespace App\Imports;

use App\Imports\Exceptions\InvalidDataException;
use App\Models\AccessLog;
use App\Models\App;
use App\Models\Category;
use App\Models\Import;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionAnswerTranslation;
use App\Models\QuestionTranslation;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionCreate;
use DB;

class QuestionsMultipleChoiceImporter extends Importer
{
    use QuestionAttachable;

    protected $necessaryHeaders = [
        'question',
        'answer1',
        'answer1_correct',
        'answer2',
        'answer2_correct',
    ];

    private array $headers;

    /**
     * @param $additionalData
     * @param $questions
     * @param $headers
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
                $question->type = Question::TYPE_MULTIPLE_CHOICE;
                $question->save();
                $questionTranslation = new QuestionTranslation();
                $questionTranslation->question_id = $question->id;
                $questionTranslation->title = $this->getDataPoint($questionData, $this->headers, 'question');
                $questionTranslation->language = $app->getLanguage();
                $questionTranslation->save();

                $feedbackWasSaved = false;

                for ($i = 1; $i <= 5; $i++) {
                    // Answers 3-5 are optional
                    if ($i > 2) {
                        $hasAnswer = false;
                        $hasAnswerCorrect = false;
                        foreach ($this->headers as $idx => $headerEntry) {
                            if ($headerEntry === 'answer'.$i) {
                                $hasAnswer = true;
                            }
                        }

                        foreach ($this->headers as $idx => $headerEntry) {
                            if ($headerEntry === 'answer'.$i.'_correct') {
                                $hasAnswerCorrect = true;
                            }
                        }
                        if (! $hasAnswerCorrect || ! $hasAnswer) {
                            continue;
                        } else {
                            // Skip optional answers without content
                            if(!$this->getDataPoint($questionData, $this->headers, 'answer'.$i)) {
                                continue;
                            }
                        }
                    }
                    $answer = new QuestionAnswer();
                    $answer->question_id = $question->id;
                    $answer->correct = intval($this->getDataPoint($questionData, $this->headers, 'answer'.$i.'_correct')) === 1;
                    $answer->save();
                    $answerTranslation = new QuestionAnswerTranslation();
                    $answerTranslation->question_answer_id = $answer->id;
                    $answerTranslation->language = $app->getLanguage();
                    $answerTranslation->content = $this->getDataPoint($questionData, $this->headers, 'answer'.$i);
                    if ($this->hasData($this->headers, 'feedback') && $answer->correct && !$feedbackWasSaved) {
                        $answerTranslation->feedback = $this->getDataPoint($questionData, $this->headers, 'feedback');
                        $feedbackWasSaved = true;
                    }
                    $answerTranslation->save();
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
