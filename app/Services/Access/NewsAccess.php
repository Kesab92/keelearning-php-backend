<?php

namespace App\Services\Access;

use App\Models\News;
use App\Models\User;
use Exception;

class NewsAccess implements AccessInterface
{
    /**
     * @param User $user
     * @param News $resource
     * @return bool
     * @throws Exception
     */
    public function hasAccess(User $user, $resource)
    {
        if (! $resource instanceof News) {
            throw new Exception('Invalid use of NewsAccess class');
        }

        return News::visibleToUser($user)
            ->where('id', $resource->id)
            ->exists();
    }
}
