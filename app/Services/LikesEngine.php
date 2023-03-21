<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\Courses\Course;
use App\Models\LearningMaterial;
use App\Models\Like;
use App\Models\News;
use App\Models\User;
use App\Services\Access\AccessFactory;
use App\Services\Access\AccessInterface;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LikesEngine
{
    /**
     * Returns a bool if the user likes the resource.
     *
     * @param User $user
     * @param int $foreign_type
     * @param int $foreign_id
     * @return bool
     */
    public function likesIt(User $user, int $foreign_type, int $foreign_id)
    {
        return Like::where('user_id', $user->id)
                ->where('foreign_type', $foreign_type)
                ->where('foreign_id', $foreign_id)
                ->exists();
    }

    /**
     * Persists, that a user likes a resource.
     *
     * @param User $user
     * @param int $foreign_type
     * @param int $foreign_id
     * @return bool Returns true if the user now likes the resource
     * @throws Exception
     */
    public function like(User $user, int $foreign_type, int $foreign_id)
    {
        if ($this->likesIt($user, $foreign_type, $foreign_id)) {
            return true;
        }

        $like = new Like();
        $like->user_id = $user->id;
        $like->foreign_type = $foreign_type;
        $like->foreign_id = $foreign_id;

        return $like->save();
    }

    /**
     * Persist, that a user doesn't like a resource anymore.
     *
     * @param User $user
     * @param int $foreign_type
     * @param int $foreign_id
     * @return bool
     */
    public function dislike(User $user, int $foreign_type, int $foreign_id)
    {
        return Like::where('user_id', $user->id)
            ->where('foreign_type', $foreign_type)
            ->where('foreign_id', $foreign_id)
            ->delete();
    }

    /**
     * Returns the amount of people which like the resource.
     *
     * @param int $foreign_type
     * @param int $foreign_id
     * @return int
     */
    public function likesCount(int $foreign_type, int $foreign_id)
    {
        return Like::where('foreign_type', $foreign_type)
            ->where('foreign_id', $foreign_id)
            ->count();
    }

    /**
     * @param User $user
     * @param $resource
     * @return bool
     * @throws Exception
     */
    public function hasAccess(User $user, $resource)
    {
        if (! $resource || ! $user) {
            return false;
        }
        $accessChecker = AccessFactory::getAccessChecker($resource);

        return $accessChecker->hasAccess($user, $resource);
    }

    /**
     * @param int $foreign_type
     * @param int $foreign_id
     * @return Model|object|null
     * @throws Exception
     */
    public function getResource(int $foreign_type, int $foreign_id)
    {
        $model = null;
        switch ($foreign_type) {
            case Like::TYPE_NEWS:
                $model = News::query();
                break;
            case Like::TYPE_COMPETITIONS:
                $model = Competition::query();
                break;
            case Like::TYPE_COURSES:
                $model = Course::query();
                break;
            case Like::TYPE_LEARNINGMATERIAL:
                $model = LearningMaterial::query();
                break;
        }
        if (! $model) {
            throw new Exception('Invalid foreign type');
        }

        return $model->where('id', $foreign_id)->first();
    }

    /**
     * @param $resource
     * @return int
     * @throws Exception
     */
    private function getTypeByModel($resource)
    {
        switch (get_class($resource)) {
            case News::class:
                return Like::TYPE_NEWS;
            case Competition::class:
                return Like::TYPE_COMPETITIONS;
            case Course::class:
                return Like::TYPE_COURSES;
            case LearningMaterial::class:
                return Like::TYPE_LEARNINGMATERIAL;
            default:
                throw new Exception('No type defined for this resource');
        }
    }

    /**
     * @param Collection $resources
     * @return Collection
     * @throws Exception
     */
    public function getLikesCounts(Collection $resources) : Collection
    {
        if (! $resources->count()) {
            return collect([]);
        }
        $foreignType = $this->getTypeByModel($resources->first());

        return Like::select(DB::raw('count(*) as c, foreign_id'))
            ->where('foreign_type', $foreignType)
            ->whereIn('foreign_id', $resources->pluck('id'))
            ->groupBy('foreign_id')
            ->pluck('c', 'foreign_id');
    }

    /**
     * @param Collection $resources
     * @param User $user
     * @return Collection
     * @throws Exception
     */
    public function getUserLikes(Collection $resources, User $user) : Collection
    {
        if (! $resources->count()) {
            return collect([]);
        }
        $foreignType = $this->getTypeByModel($resources->first());

        return Like::select(DB::raw('foreign_id'))
            ->where('foreign_type', $foreignType)
            ->where('user_id', $user->id)
            ->whereIn('foreign_id', $resources->pluck('id'))
            ->pluck('foreign_id');
    }
}
