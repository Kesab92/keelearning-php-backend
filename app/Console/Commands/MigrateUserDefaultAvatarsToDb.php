<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MigrateUserDefaultAvatarsToDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:defaultavatars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates the default avatars to the users table.';

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
     * @return mixed
     */
    public function handle()
    {
        $usersWithoutAvatars = User::withoutGlobalScope('human')->whereNull('avatar_url');
        $this->line('Migrating user default avatars to database.');
        $bar = $this->output->createProgressBar($usersWithoutAvatars->count());
        $usersWithoutAvatars->chunkById(100, function ($users) use ($bar) {
            foreach ($users as $user) {
                $user->avatar_url = $user->getDefaultAvatar();
                $user->save();
                $bar->advance();
            }
        });
        $bar->finish();
        $this->line('');
        $this->line('Migration done');
    }
}
