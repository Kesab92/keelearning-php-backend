<?php

namespace App\Services\AccessLogMeta\Questions;

use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogQuestionAddAttachment implements AccessLogMeta
{
    /**
     * @var null
     */
    protected $attachment = null;

    /**
     * AccessLogQuestionRemoveAttachment constructor.
     * @param $attachment
     */
    public function __construct($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'type' => $this->attachment->type,
            'url' => $this->attachment->attachment,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.questions.attachments', [
            'meta' => $meta,
        ]);
    }
}
