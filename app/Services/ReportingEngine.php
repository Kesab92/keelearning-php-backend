<?php

namespace App\Services;

use App\Exports\DefaultExport;
use App\Mail\Mailer;
use App\Mail\QuizReporting;
use App\Mail\UserReporting;
use App\Models\Reporting;
use App\Models\Tag;
use App\Models\User;
use App\Services\Users\UsersStatsExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Mail;

class ReportingEngine
{
    const INTERVAL_1W = '1w';
    const INTERVAL_2W = '2w';
    const INTERVAL_1M = '1m';
    const INTERVAL_3M = '3m';
    const INTERVAL_6M = '6m';
    const INTERVAL_1Y = '1y';

    const INTERVAL_LABELS = [
        self::INTERVAL_1W => 'Wöchentlich',
        self::INTERVAL_2W => 'Zwei-Wöchentlich',
        self::INTERVAL_1M => 'Monatlich',
        self::INTERVAL_3M => 'Vierteljährlich',
        self::INTERVAL_6M => 'Halbjährlich',
        self::INTERVAL_1Y => 'Jährlich',
    ];

    public function reportingsFilterQuery($appId, $type, $orderBy = null, $descending = false)
    {
        $reportingsQuery = Reporting
            ::where('app_id', $appId)
            ->where('type', $type);

        if ($orderBy) {
            $reportingsQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }

        return $reportingsQuery;
    }

    public function getReporting($id, $user = null)
    {
        $user = $user ?? Auth::user();

        /** @var Reporting $reporting */
        $reporting = Reporting::findOrFail($id);

        if($reporting->app_id !== appId()) {
            app()->abort(404);
        }
        if(!$user->isFullAdmin()) {
            $userTagRights = $user->tagRightsRelation->pluck('id');
            $commonTags = $userTagRights->intersect($reporting->tag_ids);
            if($commonTags->isEmpty()) {
                app()->abort(404);
            }
        }
        return $reporting;
    }

    /**
     * Sends the reporting to all emails.
     *
     * @param Reporting $reporting
     * @throws \Exception
     */
    public function send(Reporting $reporting)
    {
        $mailer = app(Mailer::class);

        $tags = null;
        if ($reporting->tag_ids) {
            $tags = Tag::where('app_id', $reporting->app_id)
                ->whereIn('id', $reporting->tag_ids)
                ->pluck('label')
                ->implode(', ');
        }
        $interval = self::INTERVAL_LABELS[$reporting->interval];
        foreach ($reporting->emails as $email) {
            switch($reporting->type) {
                case Reporting::TYPE_USERS:
                    $mailer->sendUserReporting($reporting, $email, $tags, $interval);
                    break;
                case Reporting::TYPE_QUIZ:
                    $mailer->sendQuizReporting($reporting, $email, $tags, $interval);
                    break;

            }
        }
    }
}
