<?php

namespace App\Services;

use App\Mail\Mailer;
use App\Models\Competition;
use App\Stats\PlayerCorrectAnswersByCategoryBetweenDates;
use Carbon\Carbon;

class Referee
{
    /**
     * The function checks, if the competition has already ended.
     *
     * @param $competitionId
     * @return bool
     */
    public static function competitionIsEnded($competitionId)
    {

        /** @var Competition $competition */
        $competition = Competition::find($competitionId);
        $now = Carbon::now();
        $deadLine = $competition->getEndDate();

        if ($deadLine->lte($now)) {
            return true;
        }

        return false;
    }

    /**
     * The function returns false, if the competition is either still running or within the buffer of days and minutes
     * after the competition should finish. True is returned if the competition has ended and is out of the buffer.
     *
     * @param $competitionId
     * @param $days
     * @param $minutes
     * @return bool
     */
    public static function withinTimeBuffer($competitionId, $days = 0, $minutes = 0)
    {

        /** @var Competition $competition */
        $competition = Competition::find($competitionId);
        $now = Carbon::now();
        $lowerDeadLine = $competition->getEndDate();
        $upperDeadline = Carbon::parse($competition->start_at)
            ->addDays($competition->duration + $days)
            ->addMinutes($minutes);

        if ($lowerDeadLine->lte($now) && $upperDeadline->gte($now)) {
            return true;
        }

        return false;
    }

    /**
     * The function first checks if the competition is finished. If it is, emails are sent to all members that
     * participated with information on their status. The owner of the quiz team receives an email with additional
     * information about the whole quiz team.
     *
     * @param $competitionId
     */
    public static function seekAndFinishCompetition($competitionId)
    {
        /** @var CompetitionEngine $competitionEngine */
        $competitionEngine = app(CompetitionEngine::class);
        // If the competition has ended but is out of a buffer of five minutes, retrieve the information and
        // send it via email. (Because the scheduler runs every 5 minutes)
        if (self::withinTimeBuffer($competitionId, 0, 5)) {

            /** @var Competition $competition */
            $competition = Competition::find($competitionId);
            $members = $competitionEngine->getMemberStats($competition);

            // Pack the data to send
            $data = [];
            foreach ($members as $member) {
                $data[] = [
                    'userId' => $member->id,
                    'rightAnswers' => $member->stats['answersCorrect'],
                ];
            }

            // Send the emails
            $mailer = new Mailer();
            $mailer->sendCompetitionResults($data, $competition);
        }
    }
}
