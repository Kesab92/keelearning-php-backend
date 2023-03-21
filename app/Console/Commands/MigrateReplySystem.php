<?php

namespace App\Console\Commands;

use App\Models\AppProfileSetting;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Models\MailTemplate;
use App\Models\NotificationSubscription;
use App\Models\User;
use App\Models\UserNotificationSetting;
use App\Services\MorphTypes;
use DB;
use Exception;
use Illuminate\Console\Command;

class MigrateReplySystem extends Command
{
    protected $signature = 'replysystem:migrate';
    protected $description = 'Migrates the reply system to the new NotificationSubscriptions.';

    /**
     * @throws Exception
     */
    public function handle(): int
    {
        $this->line('Delete old mail templates.');
        MailTemplate::where('app_id', 0)
            ->where('type', 'CommentReply')
            ->delete();

        $this->line('Migrate CommentReply app profile settings.');
        AppProfileSetting::where('key', 'notification_CommentReply_enabled')
            ->update(['key' => 'notification_NotificationSubscription_enabled']);
        AppProfileSetting::where('key', 'notification_CommentReply_user_manageable')
            ->update(['key' => 'notification_NotificationSubscription_user_manageable']);

        $this->line('Migrate user notification settings.');
        UserNotificationSetting::where('notification', 'CommentReply')
            ->update(['notification' => 'NotificationSubscription']);

        $this->line('Subscribe users to their own comments');
        $parentComments = DB::table('comments')
            ->select([
                'id',
                'author_id',
            ])
            ->whereNull('parent_id')
            ->get();
        $bar = $this->output->createProgressBar($parentComments->count());
        foreach($parentComments as $parentComment) {
            NotificationSubscription::subscribe($parentComment->author_id, MorphTypes::TYPE_COMMENT, $parentComment->id);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->line('Subscribe users to content they commented on');
        $parentComments = DB::table('comments')
            ->select([
                'id',
                'author_id',
                'foreign_id',
                'foreign_type',
            ])
            ->whereNull('parent_id')
            ->whereIn('foreign_type', NotificationSubscription::SUBSCRIBABLES)
            ->groupBy([
                'author_id',
                'foreign_id',
                'foreign_type',
            ])
            ->get();
        $bar = $this->output->createProgressBar($parentComments->count());
        foreach($parentComments as $parentComment) {
            NotificationSubscription::subscribe($parentComment->author_id, $parentComment->foreign_type, $parentComment->foreign_id);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->line('Subscribe users to parent comments they commented on.');
        $childComments = DB::table('comments')
            ->select('comments.*')
            ->whereNotNull('comments.parent_id')
            ->leftJoin('comments AS parentComments', function($join) {
                $join->on('parentComments.id', '=', 'comments.parent_id')
                    ->on('parentComments.author_id', '!=', 'comments.author_id');
            })
            ->groupBy(['comments.author_id', 'comments.parent_id'])
            ->get();
        $bar = $this->output->createProgressBar($childComments->count());
        foreach($childComments as $childComment) {
            NotificationSubscription::subscribe($childComment->author_id, MorphTypes::TYPE_COMMENT, $childComment->parent_id);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->line('Subscribe users and course moderators/admins to todolist course content attempts.');
        $courseContentAttempts = CourseContentAttempt::with(['participation.course.managers'])
            ->whereHas('content', function ($query) {
                $query->where('type', CourseContent::TYPE_TODOLIST);
            })
            ->get();
        $bar = $this->output->createProgressBar($courseContentAttempts->count());
        foreach($courseContentAttempts as $courseContentAttempt) {
            NotificationSubscription::subscribe($courseContentAttempt->participation->user_id, MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT, $courseContentAttempt->id);
            $managers = $courseContentAttempt->participation->course->managers;
            if ($managers->isEmpty()) {
                $managers = User::ofApp( $courseContentAttempt->participation->course->app_id)
                    ->where('is_admin', 1)
                    ->whereHas('role', function ($roleQuery) {
                        $roleQuery->where('is_main_admin', 1);
                    })
                    ->get();
            }
            foreach ($managers as $manager) {
                NotificationSubscription::subscribe($manager->id, MorphTypes::TYPE_COURSE_CONTENT_ATTEMPT, $courseContentAttempt->id);
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        return 0;
    }
}
