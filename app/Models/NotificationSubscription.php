<?php

namespace App\Models;

use App\Services\MorphTypes;
use Exception;
use Illuminate\Support\Collection;

/**
 * @mixin IdeHelperNotificationSubscription
 */
class NotificationSubscription extends KeelearningModel
{
    protected $guarded = [];

    const SUBSCRIBABLES = [
        MorphTypes::TYPE_COMMENT,
        MorphTypes::TYPE_COURSE,
        MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT,
        MorphTypes::TYPE_LEARNINGMATERIAL,
        MorphTypes::TYPE_NEWS,
    ];

    public function relatable()
    {
        return $this->morphTo(__FUNCTION__, 'foreign_type', 'foreign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Subscribes a user to a given content, if not already subscribed.
     *
     * @param int $userId
     * @param int $contentType
     * @param int $contentId
     * @return NotificationSubscription
     * @throws Exception
     */
    public static function subscribe(int $userId, int $contentType, int $contentId): NotificationSubscription
    {
        if (!in_array($contentType, self::SUBSCRIBABLES)) {
            throw new Exception('Cannot subscribe to content of type '.$contentType);
        }
        $existingSubscription = self::where('user_id', $userId)
            ->where('foreign_type', $contentType)
            ->where('foreign_id', $contentId)
            ->first();
        if ($existingSubscription) {
            return $existingSubscription;
        }
        return self::create([
            'user_id' => $userId,
            'foreign_type' => $contentType,
            'foreign_id' => $contentId,
        ]);
    }

    /**
     * Gets all users subscribed to a content.
     *
     * @param int $contentType
     * @param int $contentId
     * @return Collection
     * @throws Exception
     */
    public static function subscribedUserIds(int $contentType, int $contentId): Collection
    {
        if (!in_array($contentType, self::SUBSCRIBABLES)) {
            throw new Exception('Cannot subscribe to content of type '.$contentType);
        }
        return self::where('foreign_type', $contentType)
            ->where('foreign_id',$contentId)
            ->pluck('user_id');
    }
}
