<?php

namespace App\Console\Commands;

use App\Models\Reporting;
use App\Services\ReportingEngine;
use Config;
use Illuminate\Console\Command;
use Log;

class SendReportings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reportings:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends the due reportings';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /** @var ReportingEngine $reportingEngine */
        $reportingEngine = app(ReportingEngine::class);
        foreach (Reporting::all() as $reporting) {
            /* @var Reporting $reporting */
            try {
                if ($reporting->isDue()) {
                    Config::set('app.force_language', defaultAppLanguage($reporting->app_id));
                    $this->info('Sending ' . $reporting->id);
                    $reportingEngine->send($reporting);
                }
            } catch (\Exception $e) {
                $this->error('Couldn\'t send reporting ' . $reporting->id);
                \Sentry::captureException($e);
                Log::error($e->__toString());
            }
        }
    }
}
