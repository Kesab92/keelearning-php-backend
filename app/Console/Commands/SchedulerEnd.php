<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;

class SchedulerEnd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler:end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo date('Y-m-d H:i:s').' Stop scheduler';
        $this->info(date('Y-m-d H:i:s').' Stop scheduler');
        Log::info(date('Y-m-d H:i:s').' Stop scheduler');
    }
}
