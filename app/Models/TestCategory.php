<?php

namespace App\Models;

/**
 * App\Models\TestCategory
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $test_id
 * @property int $category_id
 * @property int $question_amount
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\Test $test
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereQuestionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestCategory whereUpdatedAt($value)
 * @mixin IdeHelperTestCategory
 */
class TestCategory extends KeelearningModel
{
    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
