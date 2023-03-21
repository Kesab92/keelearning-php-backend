<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\AnalyticsEvent;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentAttachment;
use App\Models\Comments\CommentReport;
use App\Models\Courses\Course;
use App\Models\Courses\CourseContentAttempt;
use App\Models\LearningMaterial;
use App\Models\News;
use App\Models\NotificationSubscription;
use App\Services\Access\AccessFactory;
use App\Services\CommentEngine;
use App\Services\MorphTypes;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Response;
use Storage;
use Throwable;


class CommentsController extends Controller
{
    const PER_PAGE_MAX = 50;

    /**
     * Returns comments of the resource.
     *
     * @param $type
     * @param $id
     * @param Request $request
     * @param CommentEngine $commentEngine
     * @return JsonResponse
     * @throws Exception
     */
    public function commentsForResource($type, $id, Request $request, CommentEngine $commentEngine): JsonResponse
    {
        $this->checkAccessToResource($type, $id);

        $perPage = $request->input('perPage');
        if ($perPage > self::PER_PAGE_MAX) {
            $perPage = self::PER_PAGE_MAX;
        }

        $page = $request->input('page') ?? 1;
        // Sometimes we want to link to a specific comment,
        // but since we order most-recent-first, we don't know
        // which page it is on.
        $targetCommentId = $request->input('target');
        if ($targetCommentId) {
            $targetComment = Comment::where('id', $targetCommentId)
                ->where('foreign_type', $type)
                ->where('foreign_id', $id)
                ->first();
            if ($targetComment) {
                $previousCommentCount = Comment::where('foreign_type', $type)
                    ->where('foreign_id', $id)
                    ->whereNull('parent_id')
                    ->where('id', '>', $targetComment->parent_id ?: $targetComment->id)
                    ->count();
                $page = floor($previousCommentCount / $perPage) + 1;
            }
        }

        $page = max((int)$page, 1);

        $commentsQuery = Comment::where('foreign_type', $type)
            ->where('foreign_id', $id);
        $totalCount = $commentsQuery->count();

        $parentCommentsQuery = $commentsQuery->whereNull('parent_id');
        $parentCount = $parentCommentsQuery->count();

        $parentComments = $parentCommentsQuery
            ->with([
                'attachments',
                'author.app', // TODO: remove preloading app & tags once avatars dont need to be calculated anymore
                'author.tags', // https://app.asana.com/0/1201714881663950/1203499652104174
                'replies.author.app',
                'replies.author.tags',
                'replies.attachments',
            ])
            ->orderBy('id', 'desc')
            ->skip($perPage * ($page - 1))
            ->take($perPage)
            ->get();

        $parentComments = $this->getCommentsResponse($parentComments)->values();

        return Response::json([
            'comments' => $parentComments,
            'current_page' => $page,
            'parent_count' => $parentCount,
            'total_count' => $totalCount,
        ]);
    }

    /**
     * Adds the comment.
     *
     * @param int $type
     * @param int $id
     * @param Request $request
     * @param CommentEngine $commentEngine
     * @param Mailer $mailer
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(int $type, int $id, Request $request, CommentEngine $commentEngine, Mailer $mailer): JsonResponse
    {
        $this->checkAccessToResource($type, $id);

        if(count($request->files->all('files')) > CommentAttachment::ATTACHMENT_COUNT_LIMIT) {
            abort(403);
        }

        foreach($request->files->all('files') as $file) {
            if($file->getSize() > CommentAttachment::ATTACHMENT_FILESIZE_LIMIT) {
                abort(403);
            }
        }

        $filePaths = [];
        foreach ($request->files->all('files') as $index => $file) {
            $filePaths[$index] = Storage::putFileAs('uploads/comment-attachments', $file, createFilenameFromString($file->getClientOriginalName()));
        }

        $user = user();

        try {
            DB::beginTransaction();

            $comment = new Comment();
            $comment->body = $request->input('body');
            $comment->app_id = $user->app_id;
            $comment->author_id = $user->id;
            $comment->foreign_type = $type;
            $comment->foreign_id = $id;

            $parentComment = null;
            if ($request->filled('parent_id')) {
                $parentId = $request->input('parent_id');
                $parentComment = Comment::findOrFail($parentId);

                // only allow replies to top-level comments
                if ($parentComment->parent_id !== null) {
                    abort(403);
                }

                if (!$commentEngine->hasAccess($parentComment, $user)) {
                    abort(403);
                }

                if ($parentComment->foreign_type != $type || $parentComment->foreign_id != $id) {
                    abort(403);
                }

                $comment->parent_id = $parentId;
            }

            $comment->save();

            foreach ($request->files->all('files') as $index => $file) {
                $commentAttachment = new CommentAttachment();
                $commentAttachment->comment_id = $comment->id;
                $commentAttachment->file_size_kb = $file->getSize() / 1024;
                $commentAttachment->file_type = $file->getClientMimeType();
                $commentAttachment->file = $filePaths[$index];
                $commentAttachment->file_url = formatAssetURL($filePaths[$index], '3.0.0');;
                $commentAttachment->original_filename = $file->getClientOriginalName();
                $commentAttachment->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            foreach ($filePaths as $filePath) {
                Storage::delete($filePath);
            }

            DB::rollback();
            abort(500);
        }

        if ($parentComment) {
            $mailer->sendSubscriptionCommentNotification(MorphTypes::TYPE_COMMENT, $parentComment->id, $comment);
            NotificationSubscription::subscribe($user->id, MorphTypes::TYPE_COMMENT, $comment->parent_id);
        } else {
            $mailer->sendSubscriptionCommentNotification($type, $id, $comment);
            NotificationSubscription::subscribe($user->id, $type, $id);
        }


        AnalyticsEvent::log($user, AnalyticsEvent::TYPE_COMMENT_ADDED, $comment);

        return Response::json(['comment' => $this->getCommentResponse($comment)]);
    }

    /**
     * Deletes the comment
     *
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete($id)
    {
        $user = user();
        $comment = Comment::findOrFail($id);

        if ($comment->author_id !== $user->id) {
            abort(403);
        }

        $comment->deleted_at = Carbon::now();
        $comment->deleted_by_id = $user->id;
        $comment->save();

        return Response::json([]);
    }

    /**
     * Reports the comment.
     *
     * @param $id
     * @param Request $request
     * @param CommentEngine $commentEngine
     * @return JsonResponse
     * @throws Exception
     */
    public function report($id, Request $request, CommentEngine $commentEngine)
    {
        $user = user();
        $comment = Comment::findOrFail($id);

        if (!$commentEngine->hasAccess($comment, $user)) {
            abort(403);
        }

        $this->checkAccessToResource($comment->foreign_type, $comment->foreign_id);

        $availableReasons = [
            CommentReport::REASON_MISC,
            CommentReport::REASON_OFFENSIVE,
            CommentReport::REASON_ADVERTISEMENT,
            CommentReport::REASON_PERSONAL_RIGHTS,
        ];

        $reason = $request->input('reason');

        if (!in_array($reason, $availableReasons)) {
            throw new Exception('The reason does not exist');
        }

        $commentReport = new CommentReport();
        $commentReport->comment_id = $id;
        $commentReport->reporter_id = $user->id;
        $commentReport->status = CommentReport::STATUS_REPORTED;
        $commentReport->reason = $reason;

        if ($request->has('reason_explanation')) {
            $commentReport->reason_explanation = $request->input('reason_explanation');
        }
        $commentReport->save();

        return Response::json([]);
    }

    /**
     * Returns array to response.
     *
     * @param Comment $comment
     * @return array
     */
    private function getCommentResponse(Comment $comment): array
    {
        $author = $comment->author->only([
            'id',
            'username',
            'displayname'
        ]);
        $author['avatar'] = $comment->author->avatar_url;

        $body = $comment->body;
        $deletedAt = null;

        if($comment->deleted_at) {
            $body = null;
            $deletedAt = $comment->deleted_at->toDateTimeString();
        }

        $attachments = [];
        if (!$comment->deleted_at) {
            $attachments = $comment->attachments->map->only([
                'file_url',
                'file_type',
                'file_size_kb',
                'original_filename',
            ]);
        }


        return [
            'id' => $comment->id,
            'body' => $body,
            'created_at' => $comment->created_at->toDateTimeString(),
            'deleted_at' => $deletedAt,
            'deleted_by_id' => $comment->deleted_by_id,
            'author' => $author,
            'attachments' => $attachments,
            'replies' =>  $comment->parent_id ? [] : $this->getCommentsResponse($comment->replies)->values(),
        ];
    }

    /**
     * Returns array to response.
     * @param $comments
     * @return mixed
     */
    private function getCommentsResponse($comments)
    {
        if (!$comments) {
            return collect([]);
        }

        return $comments->transform(function (Comment $comment) {
            return $this->getCommentResponse($comment);
        });
    }

    /**
     * checks if a user has access to the resource.
     *
     * @param $type
     * @param $id
     * @return void
     * @throws Exception
     */
    private function checkAccessToResource($type, $id): void
    {
        $user = user();

        switch ($type) {
            case MorphTypes::TYPE_NEWS:
                $entry = News::find($id);
                break;
            case MorphTypes::TYPE_LEARNINGMATERIAL:
                $entry = LearningMaterial::find($id);
                break;
            case MorphTypes::TYPE_COURSE:
                $entry = Course::find($id);
                break;
            case MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT:
                $entry = CourseContentAttempt::find($id);
                break;
            default:
                abort(404);
                break;
        }

        if (!$entry) {
            abort(404);
        }
        $accessChecker = AccessFactory::getAccessChecker($entry);
        if (!$accessChecker->hasAccess($user, $entry)) {
            abort(403);
        }
    }
}
