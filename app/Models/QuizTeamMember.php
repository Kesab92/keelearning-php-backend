<?php

namespace App\Models;

use Illuminate\Database\Query\Builder;

/**
 * App\Models\QuizTeamMember.
 *
 * @property-read QuizTeam $quizTeam
 * @property-read User $user
 * @property int $id
 * @property int $quiz_team_id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder|QuizTeamMember whereId($value)
 * @method static Builder|QuizTeamMember whereQuizTeamId($value)
 * @method static Builder|QuizTeamMember whereUserId($value)
 * @method static Builder|QuizTeamMember whereCreatedAt($value)
 * @method static Builder|QuizTeamMember whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|QuizTeamMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuizTeamMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuizTeamMember query()
 * @mixin IdeHelperQuizTeamMember
 */
class QuizTeamMember extends KeelearningModel
{
    public function quizTeam()
    {
        return $this->belongsTo(QuizTeam::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
