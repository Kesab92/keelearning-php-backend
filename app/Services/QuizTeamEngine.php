<?php

namespace App\Services;

use App\Models\QuizTeam;
use Illuminate\Database\Eloquent\Builder;

class QuizTeamEngine
{

    /**
     * Creates a query for quiz teams using filter
     * @param $appId
     * @param null $search
     * @param null $orderBy
     * @param false $descending
     * @return Builder|QuizTeam
     */
    public function quizTeamsFilterQuery($appId, $search = null, $orderBy = null, bool $descending = false)
    {
        $quizTeamQuery = QuizTeam::where('app_id', $appId);

        if ($search) {
            $quizTeamQuery
                ->whereRaw('name LIKE ?', '%'.escapeLikeInput($search).'%')
                ->orWhere('id', extractHashtagNumber($search));
        }

        if ($orderBy) {
            $quizTeamQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }

        return $quizTeamQuery;
    }
}
