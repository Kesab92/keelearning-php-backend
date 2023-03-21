<?php

namespace App\Services\Access;

use App\Models\Competition;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;

class CompetitionAccess implements AccessInterface
{
    /**
     * @param User $user
     * @param Competition $resource
     * @return bool
     * @throws Exception
     */
    public function hasAccess(User $user, $resource)
    {
        if (! $resource instanceof Competition) {
            throw new Exception('Invalid use of CompetitionAccess class');
        }

        if ($resource->app_id !== $user->app_id) {
            return false;
        }

        $now = Carbon::now();
        if ($resource->start_at->isAfter($now)) {
            return false;
        }

        if ($resource->start_at->addDays($resource->duration + 3)->isBefore($now)) {
            return false;
        }

        $userTags = $user->tags()->pluck('tags.id');
        if ($resource->tags->count() && $resource->tags->pluck('id')->intersect($userTags)->count() === 0) {
            return false;
        }

        return true;
    }
}
