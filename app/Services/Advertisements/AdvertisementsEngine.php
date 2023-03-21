<?php

namespace App\Services\Advertisements;

use App\Models\Advertisements\Advertisement;
use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttachment;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseContentAttemptAttachment;
use App\Models\Courses\CourseParticipation;
use App\Models\User;
use App\Services\MorphTypes;
use App\Services\QuestionsEngine;
use App\Services\TranslationEngine;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AdvertisementsEngine
{
    public function getAdvertisement($id, User $user): Course
    {
        $advertisement = Advertisement::findOrFail($id);
        if ($advertisement->app_id !== $user->app_id && ! $user->isSuperAdmin()) {
            app()->abort(404);
        }

        return $advertisement;
    }

    public function advertisementFilterQuery($appId, $search = null, $tags = null, $orderBy = null, $descending = false)
    {
        /** @var Builder $userQuery */
        $advertisementsQuery = Advertisement::where('app_id', $appId);

        if ($search) {
            $advertisementsQuery
                ->where(function(Builder $q) use ($search) {
                    $q->whereRaw('name LIKE ?', '%'.escapeLikeInput($search).'%')
                      ->orWhere('id', extractHashtagNumber($search));
                });
        }

        if ($tags && count($tags)) {
            $addAdvertisementsWithoutTag = in_array(-1, $tags);
            $tags = array_filter($tags, function ($tag) {
                return $tag !== '-1';
            });
            if (count($tags)) {
                $advertisementsQuery->where(function (Builder $query) use ($tags, $addAdvertisementsWithoutTag) {
                    $query->whereHas('tags', function ($query) use ($tags) {
                        $query->whereIn('tags.id', $tags);
                    });
                    if ($addAdvertisementsWithoutTag) {
                        $query->orWhereDoesntHave('tags');
                    }
                });
            } else {
                $advertisementsQuery->doesntHave('tags');
            }
        }

        if ($orderBy) {
            $advertisementsQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }

        return $advertisementsQuery;
    }
}
