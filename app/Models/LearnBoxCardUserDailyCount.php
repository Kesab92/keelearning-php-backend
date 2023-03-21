<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperLearnBoxCardUserDailyCount
 */
class LearnBoxCardUserDailyCount extends Model
{
    protected $fillable = [
        'count',
        'date',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
