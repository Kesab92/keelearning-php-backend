<?php

namespace App\Models;

/**
 * App\Models\AppRating
 *
 * @property int $id
 * @property int $user_id
 * @property float $rating
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppRating whereUserId($value)
 * @mixin IdeHelperAppRating
 */
class AppRating extends KeelearningModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
