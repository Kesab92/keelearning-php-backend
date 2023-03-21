<?php

namespace App\Models;

/**
 * App\Models\LearningMaterialTranslation
 *
 * @property int $id
 * @property int $learning_material_id
 * @property string $language
 * @property string $title
 * @property string $description
 * @property string $link
 * @property string $file
 * @property string|null $file_url
 * @property string $file_type
 * @property int|null $file_size_kb
 * @property int|null $wbt_subtype
 * @property string|null $wbt_custom_entrypoint
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $wbt_id
 * @property int $download_disabled
 * @property-read \App\Models\LearningMaterial $learningMaterial
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereDownloadDisabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereFileSizeKb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereLearningMaterialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterialTranslation whereWbtId($value)
 * @mixin IdeHelperLearningMaterialTranslation
 */
class LearningMaterialTranslation extends KeelearningModel
{
    use \App\Traits\Duplicatable;

    const WBT_SUBTYPE_XAPI = 0;
    const WBT_SUBTYPE_SCORM = 1;

    public function learningMaterial()
    {
        return $this->belongsTo(LearningMaterial::class);
    }

    public function azureVideo()
    {
        return $this->belongsTo(AzureVideo::class, 'file');
    }
}
