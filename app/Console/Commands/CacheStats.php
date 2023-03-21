<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;

/**
 * Class CacheStats.
 */
class CacheStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Caches all player and game stats. Run this command daily';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Clear the cache');
        $this->call('cache:clear');
        try {
            $this->info('Generate player stats');
            $this->call('stats:cache:players');
        } catch (\Exception $e) {
            Log::error($e->__toString());
            \Sentry::captureException($e);
        }

        try {
            $this->info('Generate quiz team stats');
            $this->call('stats:cache:quizteams');
        } catch (\Exception $e) {
            Log::error($e->__toString());
            \Sentry::captureException($e);
        }

        try {
            $this->info('Generate question stats');
            $this->call('stats:cache:questions');
        } catch (\Exception $e) {
            Log::error($e->__toString());
            \Sentry::captureException($e);
        }

        try {
            $this->info('Generate category stats');
            $this->call('stats:cache:categories');
        } catch (\Exception $e) {
            Log::error($e->__toString());
            \Sentry::captureException($e);
        }

        try {
            $this->info('Generate competition stats');
            $this->call('stats:cache:competitions');
        } catch (\Exception $e) {
            Log::error($e->__toString());
            \Sentry::captureException($e);
        }

        try {
            $this->info('Generate challenging questions stats');
            $this->call('stats:cache:challengingquestions');
        } catch (\Exception $e) {
            Log::error($e->__toString());
            \Sentry::captureException($e);
        }

        try {
            $this->info('Generate strong players stats');
            $this->call('stats:cache:strongplayers');
        } catch (\Exception $e) {
            Log::error($e->__toString());
            \Sentry::captureException($e);
        }

        try {
            $this->info('Generate competition stats');
            $this->call('api:stats:cache:players');
        } catch (\Exception $e) {
            Log::error($e->__toString());
            \Sentry::captureException($e);
        }
    }
}
