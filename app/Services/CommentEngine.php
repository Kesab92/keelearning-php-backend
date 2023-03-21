<?php

namespace App\Services;

use App\Models\Comments\Comment;
use App\Models\Comments\CommentReport;
use App\Models\Competition;
use App\Models\Courses\Course;
use App\Models\LearningMaterial;
use App\Models\News;
use App\Models\User;
use App\Traits\TagRights;
use Illuminate\Database\Eloquent\Builder;

class CommentEngine
{
    /**
     * Create a query for comments using filter
     *
     * @param int $appId
     * @param User $user
     * @param string|null $search
     * @param array|null $tags
     * @param array|null $filters
     * @param string|null $orderBy
     * @param boolean $descending
     * @param boolean $showPersonalData
     * @return null|comment|\Illuminate\Database\Eloquent\Builder
     */
    public function commentsFilterQuery(int $appId, User $adminUser, ?string $search = null, ?array $tags = null, ?array $filters = null, ?string $orderBy = null, bool $descending = false, bool $showPersonalData = false)
    {
        $commentsQuery = Comment::where('app_id', $appId);

        if($search && $showPersonalData) {
            $commentsQuery->whereIn('author_id', User::select('id')
                ->where('app_id', $appId)
                ->whereRaw('username LIKE ?', '%'.escapeLikeInput($search).'%'));
        }

        $visibleTypes = [];
        if ($adminUser->hasRight('courses-view') || $adminUser->hasRight('courses-edit')) {
            $visibleTypes[] = MorphTypes::TYPE_COURSE;
            $visibleTypes[] = MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT;
        }
        if ($adminUser->hasRight('learningmaterials-edit')) {
            $visibleTypes[] = MorphTypes::TYPE_LEARNINGMATERIAL;
        }
        if ($adminUser->hasRight('news-edit')) {
            $visibleTypes[] = MorphTypes::TYPE_NEWS;
        }

        if (!count($visibleTypes)) {
            return null;
        }

        // if the user can't see all commentable content types, we need to filter
        if (count(array_diff(Comment::COMMENTABLES, $visibleTypes)) || !$adminUser->isFullAdmin()) {
            $commentsQuery->where(function ($query) use ($adminUser, $appId, $visibleTypes) {
                foreach ($visibleTypes as $visibleType) {
                    $query->orWhere(function ($query) use ($adminUser, $appId, $visibleType) {
                        $query->where('foreign_type', $visibleType);
                        // if the user has TAG restrictions, we need to filter
                        if (!$adminUser->isFullAdmin()) {
                            $foreignModel = array_flip(MorphTypes::MAPPING)[$visibleType];
                            // some content types do not (yet) have TAG restrictions implemented
                            if (in_array(TagRights::class, class_uses($foreignModel))) {
                                $foreignIds = $foreignModel::ofApp($appId)
                                    ->tagRights($adminUser)
                                    ->pluck('id');
                                $query->whereIn('foreign_id', $foreignIds);
                            }
                            // Course content attempts don't have TAGs directly, but the courses do
                            if($visibleType === MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT) {
                                $visibleCourseContentsQuery = Course::tagRights($adminUser)
                                    ->join('course_chapters', 'course_chapters.course_id', '=', 'courses.id')
                                    ->join('course_contents', 'course_contents.course_chapter_id', '=', 'course_chapters.id')
                                    ->join('course_content_attempts', 'course_content_attempts.course_content_id', '=', 'course_contents.id')
                                    ->select('course_content_attempts.id');
                                $query->whereIn('foreign_id', $visibleCourseContentsQuery);
                            }
                        }
                    });
                }
            });
        }

        if($filters) {
            $commentsQuery->where(function ($query) use ($filters) {
                if (in_array('type_news', $filters)) {
                    $query->orWhere('foreign_type', MorphTypes::TYPE_NEWS);
                }
                if (in_array('type_courses', $filters)) {
                    $query->orWhere('foreign_type', MorphTypes::TYPE_COURSE);
                }
                if (in_array('type_learningmaterials', $filters)) {
                    $query->orWhere('foreign_type', MorphTypes::TYPE_LEARNINGMATERIAL);
                }
                if (in_array('type_course_content_attempt', $filters)) {
                    $query->orWhere('foreign_type', MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT);
                }
            });
            $commentsQuery->where(function ($query) use ($filters) {
                if (in_array('status_deleted', $filters)) {
                    $query->orWhereNotNull('deleted_at');
                }
                if (in_array('status_unresolved', $filters)) {
                    $query->orWhereIn('id', CommentReport::select('comment_id')
                        ->where('status', CommentReport::STATUS_REPORTED));
                }
                if (in_array('status_normal', $filters)) {
                    $query->orWhere(function ($query) use ($filters) {
                        $query->whereDoesntHave('reports', function ($query) {
                            $query->where('status', CommentReport::STATUS_REPORTED);
                        })->whereNull('deleted_at');
                    });
                }
            });
        }

        if ($tags) {
            $commentsQuery->whereIn('author_id', User::select('id')->where(function ($query) use ($tags) {
                $query->where(function (Builder $query) use ($tags) {
                    $query->whereHas('tags', function ($query) use ($tags) {
                        $query->whereIn('tags.id', $tags);
                    });
                });
            }));
        }

        if ($orderBy) {
            $commentsQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }
        return $commentsQuery;
    }

    /**
     * Gets comments count for resources.
     *
     * @param $resources
     * @param User $user
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getCommentsCount($resources, User $user) {
        if (! $resources->count()) {
            return collect([]);
        }

        $foreignType = $this->getTypeByModel($resources->first());

        $comments = Comment::ofApp($user->app_id)
            ->where('foreign_type', $foreignType)
            ->whereIn('foreign_id', $resources->pluck('id'))
            ->with(['author', 'author.tags'])
            ->get();

        $commentsByResource = $comments->groupBy('foreign_id');

        return $commentsByResource->transform(function ($comments) {
            return $comments->count();
        });
    }

    /**
     * Checks if a user has access to the comment.
     * @param $comment
     * @param User $user
     * @return bool
     */
    public function hasAccess($comment, User $user)
    {
        return $comment->app_id == $user->app_id;
    }

    /**
     * @param $resource
     * @return int
     * @throws \Exception
     */
    private function getTypeByModel($resource)
    {
        switch ($resource) {
            case $resource instanceof News:
                return MorphTypes::TYPE_NEWS;
            case $resource instanceof Course:
                return MorphTypes::TYPE_COURSE;
            case $resource instanceof LearningMaterial:
                return MorphTypes::TYPE_LEARNINGMATERIAL;
            case $resource instanceof Competition:
                return MorphTypes::TYPE_COMPETITION;
            default:
                throw new \Exception('No type defined for this resource');
        }
    }
}
