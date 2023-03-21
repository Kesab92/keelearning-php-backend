<?php

namespace App\Services\AccessLogMeta\Questions;

use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogQuestionCreate implements AccessLogMeta
{
    /**
     * @var null
     */
    protected $question = null;

    /**
     * AccessLogQuestionCreate constructor.
     * @param null $question
     */
    public function __construct($question = null)
    {
        $this->question = AccessLogQuestionUpdate::createQuestionValues($question, []);
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->question;
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.questions.create', [
            'meta' => $meta,
        ]);
    }
}
