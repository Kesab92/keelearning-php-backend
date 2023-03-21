<?php

namespace App\Models;

use App\Models\Comments\Comment;
use App\Traits\Saferemovable;
use App\Traits\TagRights;
use App\Traits\Translatable;
use App\Traits\Views;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * App\Models\News
 *
 * @property int $id
 * @property int $app_id
 * @property string $news_tags
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $active_until
 * @property Carbon|null $published_at
 * @property Carbon|null $notification_sent_at
 * @property string|null $cover_image
 * @property string|null $cover_image_url
 * @property int $send_notification
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NewsTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comments\Comment[] $comments
 * @property-read int|null $comment_count
 * @property-read mixed $views
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Viewcount[] $viewcounts
 * @property-read int|null $viewcounts_count
 * @method static \Illuminate\Database\Eloquent\Builder|News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News query()
 * @method static \Illuminate\Database\Eloquent\Builder|News tagRights()
 * @method static \Illuminate\Database\Eloquent\Builder|News tagRightsJoin($tagIds = null)
 * @method static \Illuminate\Database\Eloquent\Builder|News visibleToUser(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereActiveUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereCoverImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereNewsTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereNotificationSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereSendNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|News whereUpdatedAt($value)
 * @mixin IdeHelperNews
 */
class News extends KeelearningModel
{
    use HasFactory;
    use Saferemovable;
    use TagRights;
    use Translatable;
    use Views;

    protected $dates = [
        'created_at',
        'updated_at',
        'active_until',
        'published_at',
        'notification_sent_at',
    ];
    public $translated = ['title', 'content'];

    const MAIL_EXCERPT_WORD_LENGTH = 120;

    public static function isValidCoverType($mimeType)
    {
        return in_array(strtolower($mimeType), [
            'image/jpeg',
            'image/png',
            'image/gif',
        ]);
    }

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class);
    }

    public function tags()
    {
        return $this->belongsToMany(\App\Models\Tag::class)->withTimestamps();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'foreign');
    }

    public function hasEndDate()
    {
        return $this->getRawOriginal('active_until') && $this->getRawOriginal('active_until') != '0000-00-00 00:00:00';
    }

    public function hasPublishedAtDate()
    {
        return $this->attributes['published_at'] && $this->attributes['published_at'] != '0000-00-00 00:00:00';
    }

    /**
     * Gets the mail excerpt for the news entry,
     * truncated to a certain length and with all tags except links stripped.
     */
    public function getExcerpt()
    {
        $excerpt = utrim(html_entity_decode($this->content));
        $excerpt = str_replace(['<br>', '</p>'], "\n", $excerpt);
        $excerpt = strip_tags($excerpt, '<a>');
        $words = str_word_count($excerpt, 2);
        if (count($words) > self::MAIL_EXCERPT_WORD_LENGTH) {
            $words = array_slice($words, 0, self::MAIL_EXCERPT_WORD_LENGTH, true);
            end($words);
            $position = key($words) + strlen(current($words));
            $excerpt = utrim(substr($excerpt, 0, $position)).'…';
        }
        // unclosed <a> tag?
        if (substr_count($excerpt, '<a') > substr_count($excerpt, '</a>')) {
            $excerpt .= '</a>';
        }

        return $excerpt;
    }

    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    /**
     * Only include news visible to a specified user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleToUser($query, User $user)
    {
        $userTags = $user->tags()->pluck('tags.id');

        return $query->where('app_id', $user->app_id)
            ->where(function ($query) use ($userTags) {
                $query->doesntHave('tags')
                    ->orWhereHas('tags', function ($query) use ($userTags) {
                        $query->whereIn('tags.id', $userTags);
                    });
            })
            ->where(function ($query) {
                $query->where('active_until', '>', date('Y-m-d H:i:s'))
                    ->orWhereNull('active_until');
            })
            ->where(function ($query) {
                $query->where('published_at', '<', Carbon::now());
            });
    }
}
