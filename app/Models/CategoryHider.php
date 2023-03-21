<?php

namespace App\Models;

/**
 * App\Models\Category.
 *
 * @property int $id
 * @property int $category_id
 * @property int $scope
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider s()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryHider whereUpdatedAt($value)
 * @mixin IdeHelperCategoryHider
 */
class CategoryHider extends KeelearningModel
{
    use \App\Traits\Duplicatable;

    const SCOPE_QUIZ = 1;
    const SCOPE_TRAINING = 2;

    public static function scopes()
    {
        return [
            self::SCOPE_QUIZ => 'Duellmodus',
            self::SCOPE_TRAINING => 'Trainingsmodus',
        ];
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }
}
