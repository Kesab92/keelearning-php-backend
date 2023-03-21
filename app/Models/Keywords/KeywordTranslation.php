<?php

namespace App\Models\Keywords;

use App\Models\KeelearningModel;

/**
 * App\Models\Keywords\KeywordTranslation
 *
 * @property int $id
 * @property int $keyword_id
 * @property string $language
 * @property string|null $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Keywords\Keyword $keyword
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereKeywordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KeywordTranslation whereUpdatedAt($value)
 * @mixin \Eloquent
 * @mixin IdeHelperKeywordTranslation
 */
class KeywordTranslation extends KeelearningModel
{
    public function keyword()
    {
        return $this->belongsTo(Keyword::class);
    }
}
