<?php

namespace App\Models;

use App\Services\MorphTypes;
use App\Traits\Duplicatable;
use App\Traits\Saferemovable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Reminder
 *
 * @property int $id
 * @property string $foreign_id
 * @property int $app_id
 * @property int|null $user_id
 * @property int $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $days_offset
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ReminderMetadata[] $metadata
 * @property-read int|null $metadata_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereDaysOffset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reminder whereUserId($value)
 * @mixin IdeHelperReminder
 */
class Reminder extends KeelearningModel
{
    use Saferemovable;
    use Duplicatable;
    /**
     * Defines that this reminder notifies one or more users to finish their tests.
     */
    const TYPE_USER_TEST_NOTIFICATION = 0;

    /**
     * Defines that this reminder sends test results to peoples email address.
     */
    const TYPE_TEST_RESULTS = 1;
    /**
     * Defines that this reminder notifies one or more users to finish their courses.
     */
    const TYPE_USER_COURSE_NOTIFICATION = 2;

    /**
     * Defines that this reminder sends course results to the admins.
     */
    const TYPE_ADMIN_COURSE_NOTIFICATION = 3;

    const TYPES = [
        MorphTypes::TYPE_COURSE => [
            self::TYPE_USER_COURSE_NOTIFICATION,
            self::TYPE_ADMIN_COURSE_NOTIFICATION,
        ],
        MorphTypes::TYPE_TEST => [
            self::TYPE_USER_TEST_NOTIFICATION,
            self::TYPE_TEST_RESULTS,
        ],
    ];

    /**
     * @return HasMany
     */
    public function metadata()
    {
        return $this->hasMany(ReminderMetadata::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
