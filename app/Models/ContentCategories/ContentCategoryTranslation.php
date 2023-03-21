<?php

namespace App\Models\ContentCategories;

use App\Models\KeelearningModel;

/**
 * App\Models\ContentCategories\ContentCategoryTranslation
 *
 * @property int $id
 * @property int $content_category_id
 * @property string $language
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ContentCategories\ContentCategory $contentCategory
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereContentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryTranslation whereUpdatedAt($value)
 * @mixin IdeHelperContentCategoryTranslation
 */
class ContentCategoryTranslation extends KeelearningModel
{
    public function contentCategory()
    {
        return $this->belongsTo(ContentCategory::class);
    }
}
