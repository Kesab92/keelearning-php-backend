<?php

namespace App\Models\ContentCategories;

use App\Models\App;
use App\Models\KeelearningModel;
use App\Traits\Saferemovable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ContentCategories\ContentCategory
 *
 * @property int $id
 * @property int $app_id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContentCategories\ContentCategoryTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ContentCategories\ContentCategoryRelation[] $contentCategoryRelations
 * @property-read int|null $content_category_relations_count
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 * @mixin IdeHelperContentCategory
 */
class ContentCategory extends KeelearningModel
{
    use HasFactory;
    use Saferemovable;
    use Translatable;

    const TYPE_COURSES = 'courses';
    const TYPE_KEYWORDS = 'keywords';
    const TYPE_TAGS = 'tags';
    const TYPE_FORMS = 'forms';

    public $translated = ['name'];

    /**
     * @return BelongsTo
     */
    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function contentCategoryRelations()
    {
        return $this->hasMany(ContentCategoryRelation::class);
    }
}
