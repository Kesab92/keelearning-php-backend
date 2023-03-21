<?php

namespace App\Models;

use App\Traits\Saferemovable;
use App\Traits\TagRights;
use App\Traits\Translatable;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Test.
 *
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $active_until
 * @property int|null $quiz_team_id
 * @property string $name
 * @property int|null $minutes
 * @property App $app
 * @property int $app_id
 * @property int $min_rate
 * @property int $attempts
 * @property int $repeatable_after_pass
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $archived
 * @property int $no_download
 * @property int $mode
 * @property string|null $cover_image_url
 * @property string $icon_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $awardTags
 * @property-read int|null $award_tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CertificateTemplate[] $certificateTemplates
 * @property-read int|null $certificate_templates_count
 * @property-read mixed $question_count
 * @property-read mixed $url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestSubmission[] $submissions
 * @property-read int|null $submissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestCategory[] $testCategories
 * @property-read int|null $test_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestQuestion[] $testQuestions
 * @property-read int|null $test_questions_count
 * @method static Builder|Test newModelQuery()
 * @method static Builder|Test newQuery()
 * @method static Builder|Test ofApp($appId)
 * @method static Builder|Test query()
 * @method static Builder|Test tagRights()
 * @method static Builder|Test tagRightsJoin($tagIds = null)
 * @method static Builder|Test whereActiveUntil($value)
 * @method static Builder|Test whereAppId($value)
 * @method static Builder|Test whereArchived($value)
 * @method static Builder|Test whereAttempts($value)
 * @method static Builder|Test whereCoverImageUrl($value)
 * @method static Builder|Test whereCreatedAt($value)
 * @method static Builder|Test whereQuizTeamId($value)
 * @method static Builder|Test whereIconUrl($value)
 * @method static Builder|Test whereId($value)
 * @method static Builder|Test whereMinRate($value)
 * @method static Builder|Test whereMinutes($value)
 * @method static Builder|Test whereMode($value)
 * @method static Builder|Test whereNoDownload($value)
 * @method static Builder|Test whereRepeatableAfterPass($value)
 * @method static Builder|Test whereUpdatedAt($value)
 * @mixin IdeHelperTest
 */
class Test extends KeelearningModel
{
    use Saferemovable;
    use Translatable;
    use TagRights;

    public $translated = ['name', 'description'];

    protected $dates = [
        'created_at',
        'updated_at',
        'active_until',
    ];

    const MODE_QUESTIONS = 0; // default mode, select questions
    const MODE_CATEGORIES = 1; // select n random questions per category

    /**
     * Relations.
     */
    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function testCategories()
    {
        return $this->hasMany(TestCategory::class);
    }

    public function testQuestions()
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function submissions()
    {
        return $this->hasMany(TestSubmission::class);
    }

    public function certificateTemplates()
    {
        return $this->hasMany(CertificateTemplate::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'test_tags')->withTimestamps();
    }

    public function awardTags()
    {
        return $this->belongsToMany(Tag::class, 'test_award_tags')->withTimestamps();
    }

    public function scopeTagRights($query, $admin = null)
    {
        if($admin === null) {
            $admin = Auth::user();
        }

        $tagIds = $admin->tagRightsRelation->pluck('id');
        if ($tagIds->count() == 0) {
            return $query;
        }

        return $query->where(function ($query) use ($tagIds) {
            $query->whereHas('tags', function ($tagQuery) use ($tagIds) {
                return $tagQuery->whereIn('tags.id', $tagIds);
            })
                ->orWhere(function ($query) {
                    $query->whereDoesntHave('tags')
                        ->where('quiz_team_id', '>', 0);
                });
        });
    }

    /**
     * Limits the query to the scope of tests to the app with the given id.
     *
     * @param $query
     * @param $appId
     *
     * @return mixed
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    public function points()
    {
        if ($this->mode == self::MODE_QUESTIONS) {
            return $this->testQuestions->reduce(function ($count, $testQuestion) {
                return $count + $testQuestion->realpoints;
            });
        }
        if ($this->mode == self::MODE_CATEGORIES) {
            return $this->testCategories->reduce(function ($count, $testCategory) {
                return $count + ($testCategory->category->points * $testCategory->question_amount);
            });
        }

        return null;
    }

    public function getUrlAttribute()
    {
        // seems to be unused?
        return $this->app->getDefaultAppProfile()->app_hosted_at.'/tests/'.$this->id;
    }

    public function hasEndDate()
    {
        return $this->getRawOriginal('active_until') != '0000-00-00 00:00:00' && $this->getRawOriginal('active_until') !== null;
    }

    public function hasCertificateTemplate()
    {
        $template = $this->certificateTemplates->first();
        if (! $template) {
            return false;
        }
        // set i18n app id in case we're calling from the certificate render process
        // which misses regular API authentication
        $template->setAppId($this->app_id);

        return $template->background_image && $template->elements && $template->background_image_size;
    }

    /**
     * Returns ids of participants.
     * @return QuizTeamMember|array|\Illuminate\Support\Collection
     */
    public function participantIds()
    {
        if ($this->quiz_team_id) {
            $userIds = QuizTeamMember::where('quiz_team_id', $this->quiz_team_id)
                ->select('user_id');
            $userIds = User::whereIn('id', $userIds)->whereNull('deleted_at')->pluck('id');
        } elseif ($this->tags()->count()) {
            $userIds = User::where('users.app_id', $this->app_id)
                ->join('tag_user', 'tag_user.user_id', 'users.id')
                ->whereIn('tag_user.tag_id', $this->tags()->pluck('tags.id'))
                ->whereNull('users.deleted_at')
                ->pluck('users.id');
        } else {
            $userIds = User::where('app_id', $this->app_id)->whereNull('deleted_at')->pluck('id');
        }

        return $userIds;
    }

    public function isRepeatable(User $user)
    {
        //  we used to have only a `repeatable` binary flag in the DB
        // now replaced by the attempts count
        if (! $this->attempts) {
            return true;
        }

        return TestSubmission::where('test_id', $this->id)
                ->where('user_id', $user->id)
                ->whereNotNull('result')
                ->count() < $this->attempts;
    }

    public function getQuestionCountAttribute()
    {
        switch ($this->mode) {
            case self::MODE_QUESTIONS:
                return $this->testQuestions()->count();
            case self::MODE_CATEGORIES:
                return $this->testCategories()->sum('question_amount');
        }

        return null;
    }

    public function hasReminders() {
        $reminders = Reminder::where('foreign_id', $this->id)
            ->with('user')
            ->where(function ($query) {
                return $query->where('type', Reminder::TYPE_USER_TEST_NOTIFICATION)
                    ->orWhere('type', Reminder::TYPE_TEST_RESULTS);
            });
        if (!Auth::user()->isFullAdmin()) {
            $reminders->where('user_id', Auth::user()->id);
        }
        return (bool) $reminders->count();
    }
}
