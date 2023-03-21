<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\User;
use Illuminate\Console\Command;

class TrimUsernames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:trimusernames {appid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trims all usernames of a given app';

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
    public function handle(): int
    {
        $app = App::find($this->argument('appid'));
        if (!$app) {
            $this->error('Could not find app with id #'.$this->argument('appid'));
            return 0;
        }
        $this->info('Checking users of '.$app->name);
        $leadingCount = User::ofApp($app->id)->whereRaw('username LIKE " %"')->count();
        $trailingCount = User::ofApp($app->id)->whereRaw('username LIKE "% "')->count();

        $this->info('There are '.$leadingCount.' usernames with leading whitespace and '.$trailingCount.' usernames with trailing whitespace.');
        if (!($leadingCount+$trailingCount)) {
            return 0;
        }

        $users = User::ofApp($app->id)
            ->where(function ($query) {
                $query->whereRaw('username LIKE " %"')
                    ->orWhereRaw('username LIKE "% "');
            })
            ->get();

        $this->info($users->count().' users affected.');

        if (!$app->uniqueUsernames()) {
            $this->info('App DOES NOT require unique usernames');
            if (!$this->confirm('Should usernames be trimmed?')) {
                return 1;
            }
            foreach ($users as $user) {
                $user->username = utrim($user->username);
                $user->save();
            }
            $this->info('Trimmed all usernames!');
            return 0;
        }

        $this->info('App REQUIRES unique usernames');

        $allUsernames = User::ofApp($app->id)
            ->pluck('username', 'id');
        $trimmedUsernames = $allUsernames->values()->map(function ($username) {
            return utrim($username);
        });
        $duplicateCount = $trimmedUsernames->count() - $trimmedUsernames->unique()->count();

        $this->info('There are '.$duplicateCount.' duplicate usernames.');
        if ($duplicateCount) {
            $this->info('Those might not be the same users as the ones with superfluous whitespace.');
            $this->info('Duplicate usernames will be fixed if they also need trimming. Other duplicates will be left alone.');
        }

        if (!$this->confirm('Should usernames be trimmed?')) {
            return 1;
        }

        $usernameIds = [];

        foreach ($allUsernames as $id => $username) {
            $username = utrim($username);
            if (!isset($usernameIds[$username])) {
                $usernameIds[$username] = [];
            }
            $usernameIds[$username][] = $id;
        }

        foreach ($users as $user) {
            $newUsername = utrim($user->username);
            $counter = 0;
            while (
                isset($usernameIds[$newUsername])
                && count($usernameIds[$newUsername]) >= 1
                && $usernameIds[$newUsername][0] != $user->id
            ) {
                $counter += 1;
                $newUsername = utrim($user->username).' '.$counter;
            }

            $user->username = $newUsername;
            $user->save();

            if ($counter) {
                $usernameIds[$newUsername] = [$user->id];
            }
        }

        $this->info('Trimmed all usernames!');
        return 0;
    }
}
