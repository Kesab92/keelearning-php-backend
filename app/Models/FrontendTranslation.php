<?php

namespace App\Models;

/**
 * App\Models\FrontendTranslation
 *
 * @property int $id
 * @property int $app_id
 * @property string $language
 * @property string $key
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrontendTranslation whereUpdatedAt($value)
 * @mixin IdeHelperFrontendTranslation
 */
class FrontendTranslation extends KeelearningModel
{
}
