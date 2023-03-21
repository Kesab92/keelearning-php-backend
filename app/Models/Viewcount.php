<?php

namespace App\Models;

/**
 * App\Models\Viewcount
 *
 * @property int $id
 * @property int $foreign_id
 * @property string $foreign_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $foreign
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount query()
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereForeignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Viewcount whereUserId($value)
 * @mixin IdeHelperViewcount
 */
class Viewcount extends KeelearningModel
{
    public function foreign()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfApp($query, $appId)
    {
        return $query->whereHasMorph('foreign', '*', function ($q1, $type) use ($appId) {
            switch ($type) {
                case App::class:
                    return $q1->where('id', $appId);
                case LearningMaterial::class:
                    return $q1->whereHas('learningMaterialFolder', function ($q2) use ($appId) {
                        return $q2->where('app_id', $appId);
                    });
                default:
                    return $q1->where('app_id', $appId);
            }
        });
    }
}
