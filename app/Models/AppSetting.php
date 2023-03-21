<?php

namespace App\Models;

/**
 * App\Models\AppSetting.
 *
 * @property int $id
 * @property int $app_id
 * @property string $key
 * @property string $value
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AppSetting whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AppSetting whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AppSetting whereKey($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\AppSetting whereValue($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereUpdatedAt($value)
 * @mixin IdeHelperAppSetting
 */
class AppSetting extends KeelearningModel
{
    protected $fillable = ['app_id', 'key', 'value'];

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class);
    }
}
