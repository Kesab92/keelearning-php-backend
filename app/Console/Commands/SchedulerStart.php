<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;

class SchedulerStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler:start';

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
        echo date('Y-m-d H:i:s').' Start scheduler';
        $this->info(date('Y-m-d H:i:s').' Start scheduler');
        Log::info(date('Y-m-d H:i:s').' Start scheduler');
    }
}
