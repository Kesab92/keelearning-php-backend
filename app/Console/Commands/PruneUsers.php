<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:prune {appid} {userids=0 : comma separated list of user IDs to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prunes all users of an app, with optional exceptions';

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
        $app = \App\Models\App::find($this->argument('appid'));
        if (! $app) {
            $this->error('Could not find app with id #'.$this->argument('appid'));

            return;
        }
        $this->info('Pruning users of app '.$app->name);
        $users = \App\Models\User::where('app_id', $app->id);
        if (! $this->argument('userids')) {
            $keepUsers = null;
            $this->info('No users selected for keeping.');
        } else {
            $keepUsers = explode(',', $this->argument('userids'));
            $users->whereNotIn('id', $keepUsers);
            $this->info(count($keepUsers).' users selected for keeping.');
        }

        if (! $this->confirm('Is this what you want to do?')) {
            return;
        }

        $users = $users->get();
        $this->line('Deleting '.$users->count().' users… Patience please.');
        $bar = $this->output->createProgressBar($users->count());
        $errors = [];

        foreach ($users as $user) {
            $result = $user->safeRemove();
            if ($result->success !== true) {
                $errors[] = 'Could not delete user '.$user->username.': '.implode(', ', $result->messages);
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        foreach ($errors as $error) {
            $this->comment($error);
        }

        $this->info('Finished!');
    }
}
