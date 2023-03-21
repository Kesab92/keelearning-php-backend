<?php

namespace App\Models;

/**
 * App\Models\AppProfileSetting
 *
 * @property int $id
 * @property int $app_profile_id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\AppProfile $appProfile
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereAppProfileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppProfileSetting whereValue($value)
 * @mixin IdeHelperAppProfileSetting
 */
class AppProfileSetting extends KeelearningModel
{
    protected $fillable = ['app_profile_id', 'key', 'value'];

    const DAYS_BEFORE_USER_DELETION = 5;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appProfile()
    {
        return $this->belongsTo(AppProfile::class);
    }
}
