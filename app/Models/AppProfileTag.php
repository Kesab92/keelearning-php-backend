<?php

namespace App\Models;

/**
 * App\Models\AppProfileTag
 *
 * @property int $id
 * @property int $app_profile_id
 * @property int $tag_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AppProfile $appProfile
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereAppProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileTag whereUpdatedAt($value)
 * @mixin IdeHelperAppProfileTag
 */
class AppProfileTag extends KeelearningModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function appProfile()
    {
        return $this->belongsTo(AppProfile::class);
    }
}
