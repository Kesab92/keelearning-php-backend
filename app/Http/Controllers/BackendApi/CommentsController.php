<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\Comment\CommentStoreRequest;
use App\Mail\Mailer;
use App\Models\AnalyticsEvent;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReport;
use App\Models\Courses\CourseContentAttempt;
use App\Models\NotificationSubscription;
use App\Services\CommentEngine;
use App\Services\MorphTypes;
use App\Traits\PersonalData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommentsController extends Controller
{
    use PersonalData;

    const ORDER_BY = [
        'id',
        'created_at',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:comments,comments-personaldata');
        $this->personalDataRightsMiddleware('comments');
    }

    /**
     * Returns comments data
     *
     * @param Request $request
     * @param CommentEngine $commentEngine
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request, CommentEngine $commentEngine): JsonResponse
    {
        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $filters = $request->input('filters');
        $search = $request->input('search');
        $tags = $request->input('tags', []);

        $commentsQuery = $commentEngine->commentsFilterQuery(appId(), Auth::user(), $search, $tags, $filters, $orderBy, $orderDescending, $this->showPersonalData);

        if (!$commentsQuery) {
            return response()->json([
                'count' => 0,
                'comments' => [],
            ]);
        }

        $countComments = $commentsQuery->count();
        $comments = $commentsQuery
            ->with([
                'author',
                'author.app',
                'reports',
                'reports.reporter',
                'reports.statusManager',
            ])->with(['commentable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    CourseContentAttempt::class => ['content.chapter'],
                ]);
            }])
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();

        return response()->json([
            'count' => $countComments,
            'comments' => $this->getCommentsResponse($comments),
        ]);
    }

    /**
     * Deletes the comment
     * @param $id
     * @param Request $request
     * @param Mailer $mailer
     * @return JsonResponse
     * @throws Throwable
     */
    public function delete($id, Request $request, Mailer $mailer): JsonResponse
    {
        DB::transaction(function () use ($mailer, $request, $id) {
            $comment = $this->getComment($id);
            $comment->deleted_at = Carbon::now();
            $comment->deleted_by_id = Auth::user()->id;
            $comment->save();

            $statusExplanation = null;

            if(count($comment->reports) > 0) {
                $report = $comment->reports->first();
                $report->status = CommentReport::STATUS_PROCESSED_JUSTIFIED;
                $report->status_explanation = $request->input('status_explanation');
                $report->status_manager_id = Auth::user()->id;
                $report->save();

                $statusExplanation = $report->status_explanation;
                $mailer->sendDeletedCommentNotificationForReporter($report->reporter, $comment, $statusExplanation);
            }

            $mailer->sendDeletedCommentNotificationForAuthor($comment->author, $comment, $statusExplanation);
        });

        return response()->json([]);
    }

    /**
     * Marks the comment as harmless
     * @param $id
     * @param Request $request
     * @param Mailer $mailer
     * @return JsonResponse
     * @throws Throwable
     */
    public function markAsHarmless($id, Request $request, Mailer $mailer): JsonResponse
    {
        DB::transaction(function () use ($mailer, $request, $id) {
            $comment = $this->getComment($id);
            $reports = $comment->reports()
                ->where('status', CommentReport::STATUS_REPORTED)
                ->get();
            CommentReport::whereIn('id', $reports->pluck('id'))->update([
                'status' => CommentReport::STATUS_PROCESSED_UNJUSTIFIED,
                'status_explanation' => $request->input('status_explanation'),
                'status_manager_id' => Auth::user()->id,
            ]);
            $reports->each(function ($report) use ($mailer, $comment, $request) {
                $mailer->sendNotDeletedCommentNotification($report->reporter, $comment, $request->input('status_explanation'));
            });
        });
        return response()->json([]);
    }

    /**
     * Reply to the given comment
     * @param $id
     * @param CommentStoreRequest $request
     * @param Mailer $mailer
     * @return JsonResponse
     * @throws Exception
     */
    public function reply($id, CommentStoreRequest $request, Mailer $mailer): JsonResponse {
        $parentComment = $this->getComment($id);

        $comment = new Comment();
        $comment->body = $request->input('body');
        $comment->app_id = appId();
        $comment->author_id = Auth::user()->id;
        $comment->foreign_type = $parentComment->foreign_type;
        $comment->foreign_id = $parentComment->foreign_id;
        if($parentComment->parent_id) {
            $comment->parent_id = $parentComment->parent_id;
        } else {
            $comment->parent_id = $parentComment->id;
        }
        $comment->save();

        $mailer->sendSubscriptionCommentNotification(MorphTypes::TYPE_COMMENT, $comment->parent_id, $comment);
        NotificationSubscription::subscribe(Auth::user()->id, MorphTypes::TYPE_COMMENT, $comment->parent_id);

        AnalyticsEvent::log(Auth::user(), AnalyticsEvent::TYPE_COMMENT_ADDED, $comment);

        return response()->json([]);
    }

    /**
     * Responses unresolved comments count
     *
     * @param CommentEngine $commentEngine
     * @return JsonResponse
     * @throws Exception
     */
    public function unresolvedCommentsCount(CommentEngine $commentEngine): JsonResponse
    {

        $commentsQuery = $commentEngine->commentsFilterQuery(appId(), Auth::user(), null, null, ['status_unresolved']);

        if (!$commentsQuery) {
            return response()->json([
                'unresolvedCount' => 0,
            ]);
        }

        return response()->json([
            'unresolvedCount' => $commentsQuery->count(),
        ]);
    }

    /**
     * Returns comments for the entry.
     *
     * @param $foreignType
     * @param $foreignId
     * @return JsonResponse
     * @throws Exception
     */
    public function commentsForEntry($foreignType, $foreignId): JsonResponse
    {
        $allComments = Comment::where('app_id', appId())
            ->where('foreign_type', $foreignType)
            ->where('foreign_id', $foreignId)
            ->orderBy('id', 'desc')
            ->get();

        $allComments->load([
            'attachments',
            'author',
            'author.app',
            'reports',
            'reports.reporter',
            'reports.statusManager',
            'commentable',
        ]);

        $comments = $allComments->whereNull('parent_id');

        $comments = $this->getCommentsResponse($comments)
            ->transform(function ($comment) use ($allComments) {
                $replies = $allComments->where('parent_id', $comment['id']);
                $comment['replies'] = $this->getCommentsResponse($replies)->values();
                return $comment;
            });

        return response()->json([
            'comments' => $comments->values(),
        ]);
    }

    /**
     * Returns array to response
     * @param $comments
     * @return mixed
     */
    private function getCommentsResponse($comments)
    {
        if(!$comments) {
            return collect([]);
        }
        return $comments->transform(function(Comment $comment) {
            if ($this->showPersonalData) {
                $author = $comment->author->only([
                    'id',
                    'username',
                ]);
                $author['avatar'] = $comment->author->avatar_url;
            } else {
                $author = $comment->author->only(['id']);
            }

            if($comment->commentable) {
                $commentable = $comment->commentable->only([
                    'id',
                    'title',
                ]);
                if($comment->foreign_type === MorphTypes::TYPE_LEARNINGMATERIAL) {
                    $commentable['learning_material_folder_id'] = $comment->commentable->learning_material_folder_id;
                }
                if($comment->foreign_type === MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT) {
                    $commentable['participation_id'] = $comment->commentable->course_participation_id;
                    $commentable['course_id'] = $comment->commentable->content->chapter->course_id;
                    $commentable['content_id'] = $comment->commentable->content->id;
                    $commentable['title'] = $comment->commentable->content->title;
                }
            } else {
                $commentable = [];
            }

            $reports = $comment->reports->map(function ($report) {
                if ($this->showPersonalData) {
                    $report->reporter = $report->reporter->only([
                        'id',
                        'username',
                    ]);
                } else {
                    $report->reporter = $report->reporter->only(['id']);
                }
                if ($report->statusManager) {
                    $report->statusManager = $report->statusManager->only([
                        'id',
                        'username',
                    ]);
                }
                return $report->only([
                    'id',
                    'status',
                    'reason',
                    'reason_explanation',
                    'reporter',
                    'statusManager',
                    'status_explanation',
                    'created_at',
                ]);
            });

            $attachments = $comment->attachments->map(function ($attachment) {
                return $attachment->only([
                    'id',
                    'file_url',
                    'original_filename',
                ]);
            });

            return [
                'id' => $comment->id,
                'parent_id' => $comment->parent_id,
                'body' => $comment->body,
                'created_at' => $comment->created_at,
                'author' => $author,
                'commentable' => $commentable,
                'foreign_type' => $comment->foreign_type,
                'foreign_id' => $comment->foreign_id,
                'deleted_at' => $comment->deleted_at,
                'reports' => $reports,
                'attachments' => $attachments,
            ];
        });
    }

    /**
     * Gets the comment
     *
     * @param $commentId
     * @return Comment
     * @throws Exception
     */
    private function getComment($commentId): Comment
    {
        $comment = Comment::findOrFail($commentId);

        // Check the access rights
        if ($comment->app_id != appId()) {
            app()->abort(403);
        }

        return $comment;
    }
}
