<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\UserNotificationSetting
 *
 * @property int $id
 * @property int $user_id
 * @property string $mail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserNotificationSetting whereUserId($value)
 * @mixin IdeHelperUserNotificationSetting
 */
class UserNotificationSetting extends KeelearningModel
{
    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
