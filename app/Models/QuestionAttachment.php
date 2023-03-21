<?php

namespace App\Models;

/**
 * Class QuestionAttachment.
 *
 * @property-read Question $question
 * @property int $id
 * @property int $question_id
 * @property int $type
 * @property string $url
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $attachment
 * @property string|null $attachment_url
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereAttachment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereAttachmentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAttachment whereUpdatedAt($value)
 * @mixin IdeHelperQuestionAttachment
 */
class QuestionAttachment extends KeelearningModel
{
    use \App\Traits\Duplicatable;

    const ATTACHMENT_TYPE_IMAGE = 0;
    const ATTACHMENT_TYPE_AUDIO = 1;
    const ATTACHMENT_TYPE_YOUTUBE = 2;
    const ATTACHMENT_TYPE_AZURE_VIDEO = 3;

    protected $appends = [
        'url',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function getUrlAttribute()
    {
        return $this->attachment;
    }

    public function attachmentLink()
    {
        // we can't link directly to azure hosted videos
        if ($this->type === self::ATTACHMENT_TYPE_AZURE_VIDEO) {
            return backendPath() . '/questions#/questions/' . $this->question_id.'/general';
        }
        return $this->attachment_url ?: $this->attachment;
    }

    public static function isValidFileType($path, $mimeType, $extension)
    {

        return in_array(strtolower($mimeType), [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/svg+xml',
            'audio/mpeg',
            'audio/mp3',
        ]);
    }
}
