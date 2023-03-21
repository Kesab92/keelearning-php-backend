<?php

namespace App\Jobs;

use App\Models\AccessLog;
use App\Models\Question;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionDelete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QuestionsRemove implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array|null
     */
    private $questionIds = null;

    /**
     * @var null
     */
    private $appId = null;

    /**
     * @var null
     */
    private $userId = null;

    /**
     * QuestionsRemoved constructor.
     * @param array $questionIds
     * @param $appId
     * @param $userId
     */
    public function __construct($questionIds, $appId, $userId)
    {
        $this->questionIds = $questionIds;
        $this->userId = $userId;
        $this->appId = $appId;
    }

    /**
     * Removes questions.
     * @param AccessLogEngine $accessLogEngine
     * @throws \Exception
     */
    public function handle(AccessLogEngine $accessLogEngine)
    {
        $questions = Question::whereIn('id', $this->questionIds)->get();
        foreach ($questions as $question) {
            if ($question->app_id != $this->appId) {
                throw new \Exception('Question can not be deleted');
            }

            $question->safeRemove();
            $accessLogEngine->log(AccessLog::ACTION_QUESTION_DELETE, new AccessLogQuestionDelete($question), $this->userId);
        }
    }
}
