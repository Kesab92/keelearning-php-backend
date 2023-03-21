<?php

namespace App\Services\AccessLogMeta\Questions;

use App\Models\Category;
use App\Models\QuestionAnswer;
use App\Models\QuestionTranslation;
use App\Services\AccessLogMeta\AccessLogDifferences;
use App\Services\AccessLogMeta\AccessLogMeta;
use App\Traits\GetsAccessLogChanges;

class AccessLogQuestionUpdate implements AccessLogMeta, AccessLogDifferences
{
    use GetsAccessLogChanges;

    /**
     * @var array
     */
    protected array $differences = [];
    protected int $questionId;

    /**
     * AccessLogQuestionUpdate constructor.
     * @param $newQuestion
     * @param $oldQuestion
     */
    public function __construct($newQuestion, $oldQuestion)
    {
        $newQuestion = self::createQuestionValues($newQuestion);
        $this->differences = $this->getDifferences($oldQuestion, $newQuestion);
        $this->questionId = $newQuestion['id'];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'questionId' => $this->questionId,
            'differences' => $this->differences,
        ];
    }

    /**
     * Creates an array with values by a given question to store it as access log meta data.
     * @param $question
     * @return array
     */
    public static function createQuestionValues($question)
    {
        $category = Category::find($question->category_id);
        $questionAnswers = QuestionAnswer::where('question_id', $question->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'correct' => $item->correct,
                    'translations' => $item->allTranslationRelations->map(function ($translation) {
                        return [
                            'id' => $translation->id,
                            'language' => $translation->language,
                            'content' => $translation->content,
                            'feedback' => $translation->feedback,
                        ];
                    })->toArray(),
                ];
            })->toArray();
        $translations = QuestionTranslation::where('question_id', $question->id)
            ->get()
            ->map(function ($translation) {
                return [
                   'id' => $translation->id,
                   'language' => $translation->language,
                   'title' => $translation->title,
                   'latex' => $translation->latex,
                ];
            })->toArray();

        return [
            'id' => $question->id,
            'app_id' => $question->app_id,
            'type' => $question->type,
            'visible' => (bool) $question->visible,
            'category_id' => $question->category_id,
            'category_name' => $category ? $category->name : null,
            'realanswertime' => $question->realanswertime,
            'translations' => $translations,
            'answers' => $questionAnswers,
            'answertime' => $question->answertime,
            'confirmed' => $question->confirmed,
            'creator_user_id' => $question->creator_user_id,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.questions.update', [
            'meta' => $meta,
        ]);
    }
}
