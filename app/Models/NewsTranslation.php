<?php

namespace App\Models;

/**
 * App\Models\NewsTranslation
 *
 * @property int $id
 * @property int $news_id
 * @property string $language
 * @property string $title
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\News $news
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereNewsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation whereUpdatedAt($value)
 * @mixin IdeHelperNewsTranslation
 */
class NewsTranslation extends KeelearningModel
{
    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
