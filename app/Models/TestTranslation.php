<?php

namespace App\Models;

/**
 * App\Models\TestTranslation
 *
 * @property int $id
 * @property int $test_id
 * @property string $language
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Test $test
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestTranslation whereUpdatedAt($value)
 * @mixin IdeHelperTestTranslation
 */
class TestTranslation extends KeelearningModel
{
    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
