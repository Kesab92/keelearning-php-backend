<?php

namespace App\Models;

/**
 * App\Models\CategorygroupTranslation
 *
 * @property int $id
 * @property int $categorygroup_id
 * @property string $language
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Categorygroup $categorygroup
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereCategorygroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategorygroupTranslation whereUpdatedAt($value)
 * @mixin IdeHelperCategorygroupTranslation
 */
class CategorygroupTranslation extends KeelearningModel
{
    use \App\Traits\Duplicatable;

    public function categorygroup()
    {
        return $this->belongsTo(Categorygroup::class);
    }
}
