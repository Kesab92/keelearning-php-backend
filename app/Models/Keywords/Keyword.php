<?php

namespace App\Models\Keywords;

use App\Models\App;
use App\Models\ContentCategories\ContentCategory;
use App\Models\KeelearningModel;
use App\Traits\Saferemovable;
use App\Traits\Translatable;

/**
 * App\Models\Keywords\Keyword
 *
 * @property int $id
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Keywords\KeywordTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|ContentCategory[] $categories
 * @property-read int|null $categories_count
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword query()
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keyword whereUpdatedAt($value)
 * @mixin \Eloquent
 * @mixin IdeHelperKeyword
 */
class Keyword extends KeelearningModel
{
    use Translatable;
    use Saferemovable;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public $translated = ['name', 'description'];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function categories()
    {
        return $this->belongsToMany(ContentCategory::class, 'content_category_relations', 'foreign_id', 'content_category_id')
            ->where('content_category_relations.type', ContentCategory::TYPE_KEYWORDS)
            ->withTimestamps();
    }
}
