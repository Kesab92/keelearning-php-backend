<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Mail\Mailer;
use App\Models\User;
use Config;
use Excel;
use Hash;
use Illuminate\Console\Command;

class InviteCSVUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deprecated-not-working:users:invite
                            {csvfile : The path to the csv file, relative to storage/userimports}
                            {appid : The id of the app the users should be assigned to}
                            {tagid=0 : The id of the tag the users should be assigned to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invites the users from a csv file';
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * Emailtest constructor.
     *
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        parent::__construct();

        $this->mailer = $mailer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Config::set('excel::csv.delimiter', ';');
        $firstSheet = Excel::load(storage_path('userimports/'.$this->argument('csvfile')))->get();
        $bar = $this->output->createProgressBar($firstSheet->count());

        $firstSheet->each(function ($row) use ($bar) {
            $row = array_values($row->toArray());

            $email = utrim($row[2]);
            $name = utrim($row[0]).' '.utrim($row[1]);

            if (User::where('email', $email)->where('app_id', $this->argument('appid'))->count() > 0) {
                $this->info('Skipped '.$email);
                $bar->advance();

                return;
            }

            $password = randomPassword();

            // Create a new user
            $user = new User();
            $user->app_id = $this->argument('appid');
            $user->tos_accepted = 0;
            $user->active = 1;
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->username = $name;
            $user->is_admin = false;
            $user->save();

            $tagid = intval($this->argument('tagid'));
            if ($tagid > 0) {
                $user->tags()->sync([$tagid]);
            }

            $this->mailer->sendAppInvitation($user->app_id, $user->email, $user->id, $password);

            AnalyticsEvent::log($user, AnalyticsEvent::TYPE_USER_CREATED);

            $bar->advance();
        });
    }
}
