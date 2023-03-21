<?php

namespace App\Models\Forms;

use App\Models\ContentCategories\ContentCategory;
use App\Models\KeelearningModel;
use App\Models\App;
use App\Models\Tag;
use App\Models\User;
use App\Traits\Duplicatable;
use App\Traits\Saferemovable;
use App\Traits\TagRights;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperForm
 */
class Form extends KeelearningModel
{
    use Duplicatable;
    use HasFactory;
    use Translatable;
    use TagRights;
    use Saferemovable;

    public $translated = [
        'cover_image_url',
        'title',
    ];

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'form_tags')->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(ContentCategory::class, 'content_category_relations', 'foreign_id', 'content_category_id')
            ->where('content_category_relations.type', ContentCategory::TYPE_FORMS)
            ->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class);
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class);
    }

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function answers()
    {
        return $this->hasMany(FormAnswer::class);
    }

    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', '=', $appId);
    }

    public function scopeVisible($query)
    {
        return $query
            ->where('is_draft', 0)
            ->where('is_archived', 0);
    }
}
