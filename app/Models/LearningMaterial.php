<?php

namespace App\Models;

use App\Models\Comments\Comment;
use App\Models\Courses\CourseContent;
use App\Traits\Duplicatable;
use App\Traits\Saferemovable;
use App\Traits\Translatable;
use App\Traits\Views;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\LearningMaterial
 *
 * @property int $id
 * @property int $learning_material_folder_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property \Illuminate\Support\Carbon|null $notification_sent_at
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property int $send_notification
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearningMaterialTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comments\Comment[] $comments
 * @property-read int|null $comment_count
 * @property-read \Illuminate\Database\Eloquent\Collection|CourseContent[] $courseContents
 * @property-read int|null $course_contents_count
 * @property-read mixed $app_id
 * @property-read mixed $views
 * @property-read \App\Models\LearningMaterialFolder $learningMaterialFolder
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Viewcount[] $viewcounts
 * @property-read int|null $viewcounts_count
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereLearningMaterialFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereNotificationSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereSendNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearningMaterial whereUpdatedAt($value)
 * @mixin IdeHelperLearningMaterial
 */
class LearningMaterial extends KeelearningModel
{
    use Duplicatable;
    use Saferemovable;
    use Translatable;
    use Views;
    use HasFactory;

    public $translated = [
        'title',
        'description',
        'link',
        'file',
        'file_url',
        'file_type',
        'file_size_kb',
        'wbt_id',
        'wbt_subtype',
        'wbt_custom_entrypoint',
        'download_disabled',
        'show_watermark',
    ];

    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
        'notification_sent_at',
    ];

    public function getAppAttribute()
    {
        return $this->learningMaterialFolder->app;
    }

    public function learningMaterialFolder()
    {
        return $this->belongsTo(LearningMaterialFolder::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'learning_material_tags')->withTimestamps();
    }

    public function courseContents()
    {
        return $this->morphMany(CourseContent::class, 'relatable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'foreign');
    }

    public function getFilename($raw = false)
    {
        if ($raw) {
            $path = $this->getRawTranslation('file');
        } else {
            $path = $this->file;
        }
        if (! $path) {
            return null;
        }
        $pathParts = explode('/', $path);

        return end($pathParts);
    }

    public static function isValidFileType($path, $mimeType, $extension)
    {
        // .mmap
        if ($mimeType == 'application/zip') {
            return in_array($extension, ['zip', 'mmap']);
        }

        return in_array(strtolower($mimeType), [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/svg+xml',
            'audio/mpeg',
            'audio/mp3',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword',
            'application/mspowerpoint',
            'application/msexcel',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-excel',
            'application/vnd.ms-office',
            'application/octet-stream',
            'application/cdfv2',
            'application/zip',
            'application/x-zip-compressed',
            'application/mmap',
        ]);
    }

    public static function isValidCoverType($mimeType)
    {
        return in_array(strtolower($mimeType), [
            'image/jpeg',
            'image/png',
            'image/gif',
        ]);
    }

    public function hasMedia($raw = false)
    {
        if ($raw) {
            return $this->getRawTranslation('link') || $this->getRawTranslation('file');
        } else {
            return $this->link || $this->file;
        }
    }

    public function isImage($raw = false)
    {
        return in_array($raw ? $this->getRawTranslation('file_type') : $this->file_type, ['image/jpeg', 'image/png', 'image/gif']);
    }

    public function isLink($raw = false)
    {
        return (bool) ($raw ? $this->getRawTranslation('link') : $this->link);
    }

    public function isAudio($raw = false)
    {
        return in_array($raw ? $this->getRawTranslation('file_type') : $this->file_type, ['audio/mpeg', 'audio/mp3']);
    }

    public function isWBT($raw = false)
    {
        return in_array($raw ? $this->getRawTranslation('file_type') : $this->file_type, ['wbt']);
    }

    public function isAzureVideo($raw = false)
    {
        return in_array($raw ? $this->getRawTranslation('file_type') : $this->file_type, ['azure_video']);
    }

    public function isMiscFile($raw = false)
    {
        return $this->hasMedia($raw) && ! $this->isWBT($raw) && ! $this->isAudio($raw) && ! $this->isLink($raw) && ! $this->isImage($raw) && ! $this->isAzureVideo($raw);
    }

    public function hasPublishedAtDate()
    {
        return $this->attributes['published_at'] && $this->attributes['published_at'] != '0000-00-00 00:00:00';
    }

    public function hasNotificationSentAtDate()
    {
        return $this->attributes['published_at'] && $this->attributes['published_at'] != '0000-00-00 00:00:00';
    }

    public function getAppIdAttribute()
    {
        return $this->learningMaterialFolder ? $this->learningMaterialFolder->app_id : null;
    }

    public function getWatermarkAttribute()
    {
        if (!$this->show_watermark || !user()) {
            return null;
        }
        return user()->getFullName() . ' #' . user()->id;
    }
}
