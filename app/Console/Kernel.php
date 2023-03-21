<?php

namespace App\Console;

use App\Models\AppProfile;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        AppProfile::clearStaticCache();

        // The cron runs hourly
        $schedule->command('competitions:invite')
            ->dailyAt('00:30');
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command('api:stats:cache:players')
             ->hourly()
             ->unlessBetween('21:30', '06:00');
        $schedule->command('terminate:round')
            ->everyTenMinutes();
        $schedule->command('terminate:game')
            ->everyThirtyMinutes();
        $schedule->command('competition:finish')
            ->everyFiveMinutes();
        $schedule->command('reminder:send')
            ->dailyAt('17:00');
        $schedule->command('users:expire')
             ->dailyAt('00:00');
        $schedule->command('stats:cache')
             ->dailyAt('01:00');
        $schedule->command('voucher-users:remove')
            ->dailyAt('05:30');
        $schedule->command('reportings:send')
            ->dailyAt('06:00');
        $schedule->command('queue:ensuretagwatching')
            ->hourly();
        $schedule->command('app:bots')
            ->dailyAt('06:00');
        $schedule->command('app:reminder')
            ->dailyAt('00:30');
        $schedule->command('appointments:reminders')
            ->everyFiveMinutes();
        $schedule->command('competitions:remind')
            ->dailyAt('08:30');
        $schedule->command('app:dummy')
            ->dailyAt('03:00');
        $schedule->command('users:remindexpiration')
            ->dailyAt('00:00');
        $schedule->command('app:sendcontentnotifications')
            ->dailyAt('06:45');
        $schedule->command('app:newcoursenotification')
            ->dailyAt('07:00');
        $schedule->command('webinars:reminders')
            ->everyFiveMinutes();
        $schedule->command('app:repeatedcourses')
            ->dailyAt('06:00');
        $schedule->command('intercom:contacts')
            ->dailyAt('03:00');
        collect($schedule->events())->map(function ($event) {
            /* @var $event Event */
            $event->shouldAppendOutput = true;
            $event->output = '/var/log/scheduler.log';
        });
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        $this->load(__DIR__.'/Commands/OneOff');
        require base_path('routes/console.php');
    }
}
