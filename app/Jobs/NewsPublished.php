<?php

namespace App\Jobs;

use App\Mail\Mailer;
use App\Models\User;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewsPublished implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 1;

    /**
     * Displays all receiving users.
     * @var array
     */
    protected $news = null;

    /**
     * Sends the mail.
     * @var null
     */
    protected $mailer = null;

    /**
     * Create a new job instance.
     *
     * @param null $news
     */
    public function __construct($news = null)
    {
        $this->news = $news;
        $this->mailer = app(Mailer::class);
        $this->queue = QueuePriority::HIGH;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tagIds = $this->news->tags()->pluck('tags.id');
        $userQuery = User::where('app_id', $this->news->app_id)
                         ->where('active', 1)
                         ->whereNull('deleted_at');
        if (count($tagIds) > 0) {
            $userQuery = $userQuery->whereHas('tags', function ($query) use ($tagIds) {
                $query->whereIn('tag_user.tag_id', $tagIds);
            });
        }
        $users = $userQuery->get();
        foreach ($users as $user) {
            $this->mailer->sendNewsNotification($this->news, $user);
        }
    }

    /**
     * @return array
     */
    public function tags()
    {
        return ['appid:'.$this->news->app_id];
    }
}
