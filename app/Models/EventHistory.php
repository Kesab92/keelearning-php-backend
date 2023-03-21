<?php

namespace App\Models;

/**
 * App\Models\EventHistory
 *
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property string $foreign_id
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventHistory whereUserId($value)
 * @mixin IdeHelperEventHistory
 */
class EventHistory extends KeelearningModel
{
    /**
     * An user has been notified for an uncompleted test.
     */
    const TEST_USER_NOTIFICATION = 1;

    /**
     * An user has been attempted to solve a test.
     */
    const TEST_USER_ATTEMPT = 2;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
