<?php

namespace App\Models;

use App\Traits\Duplicatable;
use App\Traits\Saferemovable;
use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\LearningMaterialFolder
 *
 * @property int $id
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $folder_icon
 * @property string|null $folder_icon_url
 * @property int|null $parent_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearningMaterialFolderTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|LearningMaterialFolder[] $childFolders
 * @property-read int|null $child_folders_count
 * @property-read mixed $icon_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearningMaterial[] $learningMaterials
 * @property-read int|null $learning_materials_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereFolderIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereFolderIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolder whereUpdatedAt($value)
 * @mixin IdeHelperLearningMaterialFolder
 */
class LearningMaterialFolder extends KeelearningModel
{
    use Duplicatable;
    use Saferemovable;
    use Translatable;
    use HasFactory;

    public $translated = ['name'];

    protected $appends = [
        'icon_url',
    ];

    public function learningMaterials()
    {
        return $this->hasMany(LearningMaterial::class);
    }

    public function parentFolder()
    {
        return $this->belongsTo(LearningMaterialFolder::class, 'parent_id');
    }

    public function childFolders()
    {
        return $this->hasMany(LearningMaterialFolder::class, 'parent_id');
    }

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'learning_material_folder_tags')->withTimestamps();
    }

    // TODO: remove after upgrade to new frontend
    public function getIconUrlAttribute()
    {
        return $this->folder_icon;
    }
}
