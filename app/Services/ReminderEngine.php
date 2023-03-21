<?php

namespace App\Services;

use App\Mail\Mailer;
use App\Models\EventHistory;
use App\Models\Reminder;
use App\Models\Test;
use App\Models\User;
use Carbon\Carbon;

class ReminderEngine
{
    /**
     * @var \Illuminate\Foundation\Application|mixed|null
     */
    protected $mailer = null;

    /**
     * ReminderEngine constructor.
     */
    public function __construct()
    {
        $this->mailer = app(Mailer::class);
    }

    /**
     * Sends notifications to users email.
     * @param $users
     * @param Test $test
     */
    public function sendTestNotifications($users, Test $test)
    {
        // only send to users who not yet passed the test
        // and still have attempts left
        $users = $users->filter(function ($user) use ($test) {
            // infinite attempts? only users who not yet passed
            if (! $test->attempts) {
                return ! $test->submissions
                    ->where('user_id', $user->id)
                    ->where('result', 1)
                    ->count();
            }
            // only one attempt? only users who did not attempt
            if ($test->attempts == 1) {
                return ! $test->submissions
                    ->where('user_id', $user->id)
                    ->count();
            }
            // only users who did not yet pass & still have attempts
            return (! $test->submissions->where('user_id', $user->id)->where('result', 1)->count())
                && ($test->submissions->where('user_id', $user->id)->count() < $test->attempts);
        });

        foreach ($users as $user) {
            $this->mailer->sendTestReminder($user, $test);
        }
    }

    /**
     * Handles reminder.
     * @param $reminder
     * @return bool
     */
    public function handleReminder($reminder)
    {
        $test = Test::with('submissions')->findOrFail($reminder->foreign_id);

        if ($test->archived) {
            return false;
        }

        if (! $test->active_until) {
            return false;
        }

        // we're being passed all reminders, check here if we should send something out today
        if ($test->active_until->subDays($reminder->days_offset)->format('Y-m-d') != Carbon::now()->format('Y-m-d')) {
            return false;
        }

        if ($reminder->type === Reminder::TYPE_USER_TEST_NOTIFICATION) {
            $tagIds = collect([]); // Important to keep this as an empty collection, otherwise the tag rights join tries to access the Auth::user()
            if ($reminder->user) {
                $tagIds = $reminder->user->tagRightsRelation->pluck('id');
            }
            $users = User::where('app_id', $reminder->app_id)
                ->where('active', 1)
                ->whereNull('deleted_at')
                ->whereIn('users.id', $test->participantIds())
                ->tagRightsJoin($tagIds)
                ->groupBy('users.id')
                ->get();
            $this->sendTestNotifications($users, $test);
        } elseif ($reminder->type === Reminder::TYPE_TEST_RESULTS) {
            $this->sendTestResults($reminder, $test);
        }

        return true;
    }

    /**
     * @param $reminder
     * @param Test $test
     */
    public function sendTestResults(Reminder $reminder, Test $test)
    {
        $email = $reminder
            ->metadata()
            ->where('key', 'email')
            ->value('value');

        $appSettings = new AppSettings($reminder->app_id);
        $hasPersonalData = !$appSettings->getValue('hide_personal_data');
        $hasPersonalDataExternal = $hasPersonalData && !$appSettings->getValue('hide_personal_data_for_external_users');

        $this->mailer->sendTestResults($email, $test, $reminder, $hasPersonalDataExternal);
    }

    /**
     * @param $user
     * @param $test
     * @param $eventHistory
     * @return mixed
     */
    public function createHistory($user, $test, $eventHistory)
    {
        $userSubmissions = $test->submissions->where('user_id', $user->id);
        $submissions = $userSubmissions->map(function ($submission) use ($test) {
            return [
                'date' => $submission->created_at,
                'type' => EventHistory::TEST_USER_ATTEMPT,
                'meta' => [
                    'result_percentage' => $submission->percentage(),
                    'passed' => $submission->result,
                    'questions' => $submission->testSubmissionAnswers->transform(function ($answer) use ($test) {
                        return [
                            'correct'     => $answer->result,
                            'question_id' => $answer->question_id,
                        ];
                    }),
                ],
            ];
        })->values();

        $userEvents = $eventHistory
            ->where('user_id', $user->id)
            ->transform(function ($event) {
                return [
                    'date' => $event->created_at,
                    'type' => EventHistory::TEST_USER_NOTIFICATION,
                ];
            });

        return $submissions->concat($userEvents)
            ->sortByDesc('date')
            ->transform(function ($event) {
                $entry = [
                    'date' => $event['date']->toDateTimeString(),
                    'type' => $event['type'],
                ];

                if (! empty($event['meta'])) {
                    $entry['meta'] = $event['meta'];
                }

                return $entry;
            })
            ->values();
    }

    /**
     * Transforms history entries for csv.
     * @param $history
     * @return mixed
     */
    public function transformHistory($history)
    {
        $history = collect($history)->transform(function ($entry) {
            $type = null;
            if ($entry['type'] == EventHistory::TEST_USER_ATTEMPT) {
                $type = 'Test durchgeführt';
            } elseif ($entry['type'] == EventHistory::TEST_USER_NOTIFICATION) {
                $type = 'Test Benachrichtigung erhalten';
            }

            $meta = [];
            if (! empty($entry['meta']) && isset($entry['meta']['passed'])) {
                $meta['result'] = $entry['meta']['passed'] ? 'bestanden' : 'nicht bestanden';
            }

            return [
                'date' => Carbon::parse($entry['date'])->format('d.m.Y H:i'),
                'meta' =>  $meta,
                'type' => $type,
            ];
        });

        return $history;
    }
}
