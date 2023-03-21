<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateIntercomContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intercom:contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Intercom contacts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        \App\Jobs\UpdateIntercomContacts::dispatch();
        return 0;
    }
}
