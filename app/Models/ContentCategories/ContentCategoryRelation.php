<?php

namespace App\Models\ContentCategories;

use App\Models\KeelearningModel;

/**
 * App\Models\ContentCategories\ContentCategoryRelation
 *
 * @property int $id
 * @property int $content_category_id
 * @property int $foreign_id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ContentCategories\ContentCategory $contentCategory
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereContentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategoryRelation whereUpdatedAt($value)
 * @mixin IdeHelperContentCategoryRelation
 */
class ContentCategoryRelation extends KeelearningModel
{
    public function contentCategory()
    {
        return $this->belongsTo(ContentCategory::class);
    }
}
