<?php

namespace App\Models;

use App\Models\Courses\Course;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @mixin IdeHelperAnalyticsEvent
 */
class AnalyticsEvent extends KeelearningModel
{
    const UPDATED_AT = null;

    const TYPE_COURSE_START = 1;
    const TYPE_COURSE_SUCCESS = 2;
    const TYPE_QUIZ_START_VS_HUMAN = 3;
    const TYPE_QUIZ_START_VS_BOT = 4;
    const TYPE_VIEW_NEWS = 5;
    const TYPE_VIEW_HOME = 6;
    const TYPE_VIEW_LEARNING_MATERIAL = 7;
    const TYPE_VIEW_COURSE = 8;
    const TYPE_USER_CREATED = 9; // TODO: use single method for user creation across whole codebase
    const TYPE_COMMENT_ADDED = 10;
    const TYPE_FEEDBACK_SENT = 11;
    const TYPE_QUESTION_SUGGESTED = 12;
    const TYPE_TEST_SUCCESS = 13;

    const VIEW_EVENT_MAPPING = [
        News::class => self::TYPE_VIEW_NEWS,
        App::class => self::TYPE_VIEW_HOME,
        LearningMaterial::class => self::TYPE_VIEW_LEARNING_MATERIAL,
        Course::class => self::TYPE_VIEW_COURSE,
    ];

    public function app(): Relation
    {
        return $this->belongsTo(App::class);
    }

    public function foreign(): Relation
    {
        return $this->morphTo();
    }

    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

    /**
     * TAGs which are required for an admin to view this event.
     *
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * TAGs assigned to the user during creation of the event.
     *
     * @return BelongsToMany
     */
    public function userTags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'analytics_event_user_tag');
    }

    /**
     * TAGs assigned to the foreign model during creation of the event.
     *
     * @return BelongsToMany
     */
    public function foreignTags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'analytics_event_foreign_tag');
    }

    /**
     * Creates a new event after finishing the request
     *
     * @param User $user
     * @param integer $type
     * @param KeelearningModel|null $foreign
     * @return void
     */
    public static function log(User $user, int $type, ?KeelearningModel $foreign = null)
    {
        dispatch(function () use ($user, $type, $foreign) {
            $analyticsEvent = new AnalyticsEvent;
            $analyticsEvent->app_id = $user->app_id;
            if ($foreign) {
                $analyticsEvent->foreign()->associate($foreign);
            }
            $analyticsEvent->user_id = $user->id;
            $analyticsEvent->type = $type;
            $analyticsEvent->save();
            if ($foreign && $foreign->tags) {
                $analyticsEvent->foreignTags()->attach($foreign->tags->pluck('id'));
                if ($user->tags) {
                    $analyticsEvent->tags()->attach($user->tags->pluck('id')->intersect($foreign->tags->pluck('id')));
                } else {
                    $analyticsEvent->tags()->attach($user->foreign->pluck('id'));
                }
            } else {
                $analyticsEvent->tags()->attach($user->tags->pluck('id'));
            }
            $analyticsEvent->userTags()->attach($user->tags->pluck('id'));
        })->afterResponse();
    }
}
