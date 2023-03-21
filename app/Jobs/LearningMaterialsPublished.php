<?php

namespace App\Jobs;

use App\Mail\Mailer;
use App\Models\LearningMaterial;
use App\Models\User;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LearningMaterialsPublished implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 1;

    /**
     * Published learning material.
     * @var LearningMaterial|null
     */
    protected $learningMaterial = null;

    /**
     * Mailer which queues mails.
     * @var Mailer|null
     */
    protected $mailer = null;

    /**
     * @var null
     */
    protected $appId = null;

    /**
     * Create a new job instance.
     *
     * @param LearningMaterial $learningMaterial
     * @param $appId
     */
    public function __construct(LearningMaterial $learningMaterial)
    {
        $this->learningMaterial = $learningMaterial;
        $this->mailer = app(Mailer::class);
        $this->appId = $learningMaterial->learningMaterialFolder()->pluck('app_id');
        $this->queue = QueuePriority::HIGH;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tagIds = $this->learningMaterial->tags()->pluck('tags.id');
        $userQuery = User::where('app_id', $this->appId)
                         ->where('active', 1)
                         ->whereNull('deleted_at');
        if ($tagIds->count()) {
            $userQuery = $userQuery->whereHas('tags', function ($query) use ($tagIds) {
                $query->whereIn('tag_user.tag_id', $tagIds);
            });
        }
        $users = $userQuery->get();

        $folderTagIds = $this->learningMaterial->learningMaterialFolder->tags()->pluck('tags.id');
        if ($folderTagIds->count()) {
            $users = $users->filter(function ($user) use ($folderTagIds) {
                return $user->tags->whereIn('id', $folderTagIds)->count() > 0;
            });
        }

        foreach ($users as $user) {
            $this->mailer->sendLearningMaterialPublishedNotification($user, $this->learningMaterial);
        }
    }

    public function tags()
    {
        return ['appid:'.$this->appId];
    }
}
