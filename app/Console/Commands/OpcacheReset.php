<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OpcacheReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opcache:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the opcache. Works only in dev';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            Http::get('http://localhost/api/v1/opcache-reset');
        } catch (\Exception $e) {
            $this->error('Could not reset the opcache:');
            $this->error($e->getMessage());
        }
        return Command::SUCCESS;
    }
}
