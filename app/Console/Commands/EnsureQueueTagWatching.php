<?php

namespace App\Console\Commands;

use App\Models\App;
use Illuminate\Console\Command;
use Laravel\Horizon\Contracts\TagRepository;

class EnsureQueueTagWatching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:ensuretagwatching';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes sure that all queue app tags are being watched';
    /**
     * @var TagRepository
     */
    private $tags;

    /**
     * Create a new command instance.
     *
     * @param TagRepository $tags
     */
    public function __construct(TagRepository $tags)
    {
        parent::__construct();
        $this->tags = $tags;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $monitoredTags = $this->tags->monitoring();
        foreach (App::all() as $app) {
            $isWatched = in_array('appid:'.$app->id, $monitoredTags);
            if (! $isWatched) {
                $this->info('App '.$app->name.' is missing');
                $this->tags->monitor('appid:'.$app->id);
            }
            $isInternallyWatched = in_array('internal-appid:'.$app->id, $monitoredTags);
            if (! $isInternallyWatched) {
                $this->info('Internal App '.$app->name.' is missing');
                $this->tags->monitor('internal-appid:'.$app->id);
            }
        }
    }
}
