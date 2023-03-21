<?php

namespace App\Models;

/**
 * Class GamePoint.
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GamePoint whereUserId($value)
 * @mixin IdeHelperGamePoint
 */
class GamePoint extends KeelearningModel
{
    const REASON_GAME_WON = 0;
    const REASON_GAME_DRAW = 1;

    /**
     * Relations.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
