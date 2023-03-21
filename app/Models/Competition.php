<?php

namespace App\Models;

use App\Models\Comments\Comment;
use App\Traits\Saferemovable;
use App\Traits\TagRights;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * App\Models\Competition.
 *
 * @property-read \App\Models\App $app
 * @property int $id
 * @property int $app_id
 * @property int $category_id
 * @property int $quiz_team_id
 * @property int $duration
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $start_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\QuizTeam $quizTeam
 * @method static Builder|Competition whereId($value)
 * @method static Builder|Competition whereAppId($value)
 * @method static Builder|Competition whereCategoryId($value)
 * @method static Builder|Competition whereQuizTeamId($value)
 * @method static Builder|Competition whereDuration($value)
 * @method static Builder|Competition whereCreatedAt($value)
 * @method static Builder|Competition whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property string|null $title
 * @property string|null $notification_sent_at
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property string|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comments\Comment[] $comments
 * @property-read int|null $comment_count
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Competition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition query()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition tagRights()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition tagRightsJoin($tagIds = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereNotificationSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereStartAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereTitle($value)
 * @mixin IdeHelperCompetition
 */
class Competition extends KeelearningModel
{
    use Saferemovable;
    use TagRights;

    protected $dates = [
        'start_at',
        'created_at',
        'updated_at',
    ];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function quizTeam()
    {
        return $this->belongsTo(QuizTeam::class);
    }

    public function getEndDate()
    {
        $endDate = null;
        if ($this->hasStartDate()) {
            $endDate = $this->start_at->addDays($this->duration);
        } else {
            $endDate = $this->created_at->addDays($this->duration);
        }
        if ($endDate->hour === 0 && $endDate->minute === 0) {
            // An end date of 00:00 irritates users, we switch it to 23:59 to be more clear
            $endDate->subMinute();
        }

        return $endDate;
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Returns a collection of users or false, if there are no tags and there is no quiz team.
     *
     * @return User[]|bool|\Illuminate\Database\Eloquent\Collection|Collection|static
     * @throws Exception
     */
    public function members()
    {
        if ($this->quizTeam) {
            return $this->quizTeam
                ->members()
                ->with('tags')
                ->where('active', 1)
                ->whereNull('deleted_at')
                ->get();
        } elseif ($this->tags()->count() > 0) {
            // Add the users of the tags
            $members = collect();
            foreach ($this->tags as $tag) {
                $users = $tag->users()
                    ->with('tags')
                    ->whereNull('deleted_at')
                    ->where('active', 1)
                    ->where('app_id', $this->app_id)
                    ->get();
                $members = $members->merge($users);
            }

            // Remove duplicates
            $members = $members->unique('id');

            return $members;
        }

        return false;
    }
    public function comments()
    {
        return $this->morphMany(Comment::class, 'foreign');
    }

    public function getCategoryName()
    {
        if ($this->category) {
            return $this->category->name;
        }

        return __('general.all_categories');
    }

    public function hasStartDate()
    {
        return $this->attributes['start_at'];
    }
}
