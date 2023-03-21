<?php

namespace App\Models;

use App\Traits\Duplicatable;
use App\Traits\Saferemovable;

/**
 * App\Models\IndexCard
 *
 * @property int $id
 * @property int $app_id
 * @property string $front
 * @property string $back
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property string|null $json
 * @property string $type
 * @property-read \App\Models\App $app
 * @property-read \App\Models\Category|null $category
 * @property-read mixed $image_url
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereBack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereFront($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IndexCard whereUpdatedAt($value)
 * @mixin IdeHelperIndexCard
 */
class IndexCard extends KeelearningModel
{
    use Saferemovable;
    use Duplicatable;

    const TYPE_STANDARD = 'standard';
    const TYPE_IMAGEMAP = 'imagemap';

    const TYPES = [
        self::TYPE_STANDARD => 'Standard',
        self::TYPE_IMAGEMAP => 'Text-in-Bild-Zuweisung',
    ];

    protected $casts = [
        'app_id' => 'integer',
        'category_id' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    // TODO: remove after upgrade to new frontend
    public function getImageUrlAttribute()
    {
        return convertPathToLegacy($this->cover_image);
    }
}
