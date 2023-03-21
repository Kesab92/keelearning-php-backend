<?php

namespace App\Services\AccessLogMeta\Questions;

use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogQuestionDelete implements AccessLogMeta
{
    /**
     * Deleted object.
     * @var null
     */
    protected $question = null;

    /**
     * AccessLogQuestionDelete constructor.
     * @param $question
     */
    public function __construct($question)
    {
        $this->question = $question;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'question_id' => $this->question->id,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.questions.delete', [
            'meta' => $meta,
        ]);
    }
}
