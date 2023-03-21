<?php

namespace App\Models;

use App\Traits\Saferemovable;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\QuizTeam.
 *
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuizTeamMember[] $quizTeamMembers
 * @property int $id
 * @property int $app_id
 * @property int $owner_id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $members
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereId($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereName($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|QuizTeam whereOwnerId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|Competition[] $competitions
 * @property-read int|null $competitions_count
 * @property-read int|null $members_count
 * @method static Builder|QuizTeam newModelQuery()
 * @method static Builder|QuizTeam newQuery()
 * @method static Builder|QuizTeam ofApp($appId)
 * @method static Builder|QuizTeam query()
 * @mixin IdeHelperQuizTeam
 */
class QuizTeam extends KeelearningModel
{
    use Saferemovable;

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'quiz_team_members')
            ->whereNull('users.deleted_at')
            ->where('users.active', 1)
            ->withTimestamps();
    }

    public function competitions()
    {
        return $this->hasMany(Competition::class);
    }

    /**
     * Limits the query to the scope of quiz teams of the app with the given id.
     *
     * @param $query
     * @param $appId
     *
     * @return mixed
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', '=', $appId);
    }
}
