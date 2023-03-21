<?php

namespace App\Services;

use App\Models\SuggestedQuestion;

class SuggestedQuestionEngine
{

    /**
     * Creates a query for suggested questions using filter
     * @param $appId
     * @param null $orderBy
     * @param false $descending
     * @return SuggestedQuestion|\Illuminate\Database\Eloquent\Builder
     */
    public function suggestedQuestionsFilterQuery($appId, $orderBy = null, $descending = false)
    {
        $suggestedQuestionsQuery = SuggestedQuestion::where('app_id', $appId);

        if ($orderBy) {
            $suggestedQuestionsQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }
        return $suggestedQuestionsQuery;
    }
}
