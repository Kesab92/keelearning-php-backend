<?php

namespace App\Models;

use App\Traits\Duplicatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ReminderMetadata
 *
 * @property int $id
 * @property int $reminder_id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Reminder $reminder
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereReminderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReminderMetadata whereValue($value)
 * @mixin IdeHelperReminderMetadata
 */
class ReminderMetadata extends KeelearningModel
{
    use Duplicatable;
    /**
     * @var string
     */
    protected $table = 'reminders_metadata';

    /**
     * @return BelongsTo
     */
    public function reminder()
    {
        return $this->belongsTo(Reminder::class);
    }
}
