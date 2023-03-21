<?php

namespace App\Mail;

use App\Exports\DefaultExport;
use App\Models\Reminder;
use App\Models\Test;
use App\Models\User;
use App\Services\QueuePriority;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class TestResultReminder extends KeelearningNotification
{
    protected $email = null;
    protected $reminder = null;
    protected $showPersonalData = false;
    protected $test = null;
    protected bool $isAlwaysActive = true;

    /**
     * TestReminder constructor.
     * @param Reminder $reminder
     * @param Test $test
     * @param string $email
     * @param bool $showPersonalData
     */
    public function __construct(Reminder $reminder, Test $test, string $email, bool $showPersonalData)
    {
        parent::__construct();

        $this->email = $email;
        $this->reminder = $reminder;
        $this->showPersonalData = $showPersonalData;
        $this->test = $test;
        $this->queue = QueuePriority::LOW;
    }

    /**
     * Builds the mail.
     * @return TestResultReminder
     */
    public function build()
    {
        $filename = 'ergebnis-' . Str::slug($this->test->name) . '.xlsx';

        return $this->view('mail.test-results')
            ->with('test', $this->test)
            ->subject('Testergebnisse - ' . $this->test->name)
            ->attach($this->createAttachment($filename));
    }

    /**
     * Creates an attachment for the email.
     * @param $filename
     */
    public function createAttachment($filename)
    {
        $users = User::where('app_id', $this->test->app_id)
            ->whereIn('users.id', $this->test->participantIds())
            ->select('users.*')
            ->groupBy('users.id');

        if ($this->reminder->user) {
            $users = $users->tagRightsJoin($this->reminder->user->tagRightsRelation->pluck('id'));
        }

        $users = $users->get()
            ->transform(function ($user) {
                $userSubmissions = $this->test->submissions
                    ->where('user_id', $user->id);

                $passed = $userSubmissions
                        ->filter(function ($item) {
                            return $item->result > 0;
                        })
                        ->count() > 0;

                return [
                    'passed' => $passed ? 'Ja' : 'Nein',
                    'username' => $this->showPersonalData ? $user->username : null,
                    'id' => $user->id,
                ];
            });

        $data = [
            'showPersonalData' => $this->showPersonalData,
            'test' => $this->test,
            'users' => $users,
        ];
        $filepath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($filepath, Excel::raw(new DefaultExport($data, 'tests.results-csv-full'), \Maatwebsite\Excel\Excel::XLSX));

        return $filepath;
    }
}
