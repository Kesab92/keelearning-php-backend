<?php

namespace App\Models\Appointments;

use App\Models\KeelearningModel;
use App\Models\App;
use App\Models\Tag;
use App\Models\User;
use App\Traits\Duplicatable;
use App\Traits\Saferemovable;
use App\Traits\TagRights;
use App\Traits\Translatable;
use Carbon\Carbon;

/**
 * @mixin IdeHelperAppointment
 */
class Appointment extends KeelearningModel
{
    use Duplicatable;
    use Translatable;
    use TagRights;
    use Saferemovable;

    const TYPE_ONLINE = 1;
    const TYPE_IN_PERSON = 2;

    const REMINDER_TIME_UNIT_MINUTES = 1;
    const REMINDER_TIME_UNIT_HOURS = 2;
    const REMINDER_TIME_UNIT_DAYS = 3;

    protected $dates = [
        'created_at',
        'updated_at',
        'start_date',
        'end_date',
        'published_at',
    ];

    public $translated = [
        'name',
        'description',
        'cover_image_url',
    ];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'appointment_tags')->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class);
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', '=', $appId);
    }

    public function scopeVisible($query)
    {
        return $query
            ->where('is_draft', 0)
            ->where(function ($subQuery) {
                $subQuery
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', Carbon::now());
            });
    }

    public function getParticipants() {
        $users = User
            ::select('users.*')
            ->showInLists()
            ->active()
            ->where('app_id', $this->app_id);

        if($this->tags->count()) {
            $users
                ->leftJoin('tag_user', 'tag_user.user_id', '=', 'users.id')
                ->whereIn('tag_user.tag_id', $this->tags->pluck('id'))
                ->groupBy('users.id');
        }

        return $users->get();
    }
}
