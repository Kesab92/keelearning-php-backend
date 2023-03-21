<?php

namespace App\Services\AccessLogMeta;

use App\Models\Tag;
use App\Models\User;

class AccessLogUserUpdate implements AccessLogMeta
{
    private $user;
    private $tagUpdates;

    public function __construct(User $user, $tagUpdates = null)
    {
        $this->user = $user;
        $this->tagUpdates = [];
        if ($tagUpdates) {
            foreach ($tagUpdates as $groupKey => $tagIds) {
                if ($tagIds) {
                    $this->tagUpdates[$groupKey] = Tag::whereIn('id', $tagIds)->pluck('label');
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'user_id' => $this->user->id,
            'user_updates' => $this->user->getDirty(),
            'tag_updates' => $this->tagUpdates,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.userupdate', [
            'meta' => $meta,
            'user' => User::find($meta['user_id']),
        ]);
    }
}
