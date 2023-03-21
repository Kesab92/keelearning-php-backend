<?php

namespace App\Models;

use App\Services\WebinarEngine;
use Carbon\Carbon;

/**
 * App\Models\Webinar.
 *
 * @property int $id
 * @property int $app_id
 * @property string $topic
 * @property string $description
 * @property \Carbon\Carbon $starts_at
 * @property int $duration_minutes set to null for open ended
 * @property bool $send_reminder
 * @property \Carbon\Carbon $reminder_sent_at
 * @property bool $show_recordings
 * @property int $samba_id
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WebinarAdditionalUser[] $additionalUsers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WebinarParticipant[] $participants
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WebinarAdditionalUser[] $additionalExternalUsers
 * @property-read int|null $additional_external_users_count
 * @property-read int|null $additional_users_count
 * @property-read int|null $participants_count
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar query()
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereReminderSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereSambaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereSendReminder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereShowRecordings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereTopic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Webinar whereUpdatedAt($value)
 * @mixin IdeHelperWebinar
 */
class Webinar extends KeelearningModel
{
    use \App\Traits\Saferemovable;

    protected $casts = [
        'send_reminder' => 'boolean',
        'show_recordings' => 'boolean',
    ];

    protected $dates = [
        'starts_at',
        'created_at',
        'updated_at',
    ];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    /**
     * Tags define which users have access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'webinar_tags')->withTimestamps();
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Tag::class)->where('users.active', 1);
    }

    /**
     * Additional users (to those with tags) that have access
     * Both internal&external.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function additionalUsers()
    {
        return $this->hasMany(WebinarAdditionalUser::class);
    }

    /**
     * Additional external users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function additionalExternalUsers()
    {
        return $this->hasMany(WebinarAdditionalUser::class)->whereNull('user_id');
    }

    /**
     * Users that actually participated
     * both internal&external.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participants()
    {
        return $this->hasMany(WebinarParticipant::class);
    }

    /**
     * Checks if the time to join the webinar is past.
     *
     * @return bool
     */
    public function isExpired()
    {
        if ($this->duration_minutes === null) {
            return false;
        }

        return $this
            ->starts_at
            ->addMinutes($this->duration_minutes)
            ->addMinutes(WebinarEngine::WEBINAR_GRACE_JOIN_PERIOD)
            ->isBefore(Carbon::now());
    }
}
