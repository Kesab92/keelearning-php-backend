<?php

namespace App\Console\Commands;

use App\Models\App;
use Illuminate\Console\Command;

class TimeDisplay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'time:display';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays the time';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info(date('Y-m-d H:i:s'));
    }
}
