<?php

namespace App\Models;

/**
 * App\Models\LearningMaterialFolderTranslation
 *
 * @property int $id
 * @property int $learning_material_folder_id
 * @property string $language
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LearningMaterialFolder $learningMaterialFolder
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereLearningMaterialFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialFolderTranslation whereUpdatedAt($value)
 * @mixin IdeHelperLearningMaterialFolderTranslation
 */
class LearningMaterialFolderTranslation extends KeelearningModel
{
    use \App\Traits\Duplicatable;

    public function learningMaterialFolder()
    {
        return $this->belongsTo(LearningMaterialFolder::class);
    }
}
