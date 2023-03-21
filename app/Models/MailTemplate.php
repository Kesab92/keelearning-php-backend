<?php

namespace App\Models;

/**
 * App\Models\MailTemplate
 *
 * @property int $id
 * @property int $app_id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MailTemplateTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \App\Models\App $app
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplate whereUpdatedAt($value)
 * @mixin IdeHelperMailTemplate
 */
class MailTemplate extends KeelearningModel
{
    use \App\Traits\Translatable;

    public $translated = ['title', 'body'];

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class);
    }

    public static function getTemplate($type, $appId = 0)
    {
        if (! $appId) {
            $template = self::where('type', '=', $type)->where('app_id', '=', 0)->first();
        } else {
            $template = self::where('type', '=', $type)
                ->whereIn('app_id', [0, $appId])
                ->orderBy('app_id', 'DESC')
                ->first();
        }
        if (! $template) {
            throw new \Exception('Template '.$type.' for App #'.(int) $appId.' not found');
        }

        return $template;
    }

    public static function getAllTemplates($appId = 0)
    {
        if (! $appId) {
            return self::where('app_id', '=', 0)->get();
        }
        $templates = self::where('app_id', '=', $appId)->get();
        $defaults = self::where('app_id', '=', 0)->whereNotIn('type', $templates->pluck('type'))->get();

        return $templates->concat($defaults);
    }
}
