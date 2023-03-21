<?php

namespace App\Models;

/**
 * App\Models\MailTemplateTranslation
 *
 * @property int $id
 * @property int $mail_template_id
 * @property string $language
 * @property string $title
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MailTemplate $mailTemplate
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereMailTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailTemplateTranslation whereUpdatedAt($value)
 * @mixin IdeHelperMailTemplateTranslation
 */
class MailTemplateTranslation extends KeelearningModel
{
    public function mailTemplate()
    {
        return $this->belongsTo(MailTemplate::class);
    }
}
