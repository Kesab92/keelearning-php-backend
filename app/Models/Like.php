<?php

namespace App\Models;

/**
 * App\Models\Like
 *
 * @property int $id
 * @property int $foreign_type
 * @property int $foreign_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Like newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Like newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Like query()
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Like whereUserId($value)
 * @mixin IdeHelperLike
 */
class Like extends KeelearningModel
{
    const TYPE_NEWS = 0;
    const TYPE_COMPETITIONS = 1;
    const TYPE_COURSES = 2;
    CONST TYPE_LEARNINGMATERIAL = 3;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
