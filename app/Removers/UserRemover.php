<?php

namespace App\Removers;

use App\Models\AccessLog;
use App\Models\AnalyticsEvent;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReport;
use App\Models\Courses\CourseParticipation;
use App\Models\Forms\Form;
use App\Models\Forms\FormAnswer;
use App\Models\DirectMessage;
use App\Models\Game;
use App\Models\NotificationSubscription;
use App\Models\QuizTeam;
use App\Models\QuizTeamMember;
use App\Models\SuggestedQuestion;
use App\Models\Tag;
use App\Models\TestSubmission;
use App\Models\TodolistItemAnswer;
use App\Models\User;
use App\Models\VoucherCode;
use App\Models\WebinarAdditionalUser;
use Notification;

class UserRemover extends Remover
{
    /**
     * Deletes/Resets everything depending on the category.
     */
    protected function deleteDependees()
    {
        $dummyUser = User::where('app_id', $this->object->app_id)
            ->where('is_dummy', true)
            ->firstOrFail();

        $this->object->accessLogs()->delete();
        $this->object->gamePoints()->delete();
        QuizTeam::where('owner_id', $this->object->id)->update([
            'owner_id' => 0,
        ]);
        $this->object->learnBoxCards()->delete();
        $this->object->metafields()->delete();
        $this->object->openIdTokens()->delete();
        $this->object->permissions()->delete();
        $this->object->questionDifficulties()->delete();
        foreach ($this->object->suggestedQuestions as $suggestedQuestion) {
            $suggestedQuestion->safeRemove();
        }
        $this->object->trainingAnswers()->delete();
        $this->object->testSubmissions()->each(function ($entry) {
            $entry->testSubmissionAnswers()->delete();
        });
        $this->object->testSubmissions()->delete();
        $this->object->quizTeams()->detach();
        $this->object->tags()->detach();
        $games = Game::where('player1_id', $this->object->id)
            ->orWhere('player2_id', $this->object->id)
            ->get();
        foreach ($games as $game) {
            $game->safeRemove();
        }

        $this->object->comments()->update(['author_id' => $dummyUser->id]);
        AnalyticsEvent::where('app_id', $this->object->app_id)
            ->where('user_id', $this->object->id)
            ->update(['user_id' => $dummyUser->id]);

        Comment::where('deleted_by_id', $this->object->id)
            ->update(['deleted_by_id' => $dummyUser->id]);
        Comment::where('author_id', $this->object->id)
            ->update(['author_id' => $dummyUser->id]);
        CommentReport::where('reporter_id', $this->object->id)
            ->update(['reporter_id' => $dummyUser->id]);

        DirectMessage::where('sender_id', $this->object->id)
            ->update(['sender_id' => $dummyUser->id]);

        DirectMessage::where('recipient_id', $this->object->id)
            ->update(['recipient_id' => $dummyUser->id]);

        VoucherCode::where('user_id', $this->object->id)
            ->update([
               'user_id' => -1,
            ]);

        WebinarAdditionalUser
            ::where('user_id', $this->object->id)
            ->delete();

        CourseParticipation::where('user_id', $this->object->id)
            ->update(['user_id' => $dummyUser->id]);

        TodolistItemAnswer::where('user_id', $this->object->id)
            ->update(['user_id' => $dummyUser->id]);

        Form::where('created_by_id', $this->object->id)
            ->update(['created_by_id' => $dummyUser->id]);
        Form::where('last_updated_by_id', $this->object->id)
            ->update(['last_updated_by_id' => $dummyUser->id]);
        FormAnswer::where('user_id', $this->object->id)
            ->update(['user_id' => $dummyUser->id]);

        NotificationSubscription::where('user_id', $this->object->id)->delete();
    }

    /**
     * Executes the actual deletion.
     *
     * @return true
     */
    protected function doDeletion()
    {
        $this->deleteDependees();
        $this->object->forceDelete();

        return true;
    }

    /**
     * Gets amount of dependees that will be deleted/altered.
     *
     * @return array if clear of blocking dependees, array of strings if not
     */
    public function getDependees()
    {
        $id = $this->object->id;

        $quizTeamIds = QuizTeam::where('owner_id', $id)->pluck('id');
        $quizTeamMemberCount = QuizTeamMember::whereIn('quiz_team_id', $quizTeamIds)->count();

        $tagIds = Tag::where('creator_id', $id)->pluck('id');
        $tagMembers = User::whereHas('tags', function ($query) use ($tagIds) {
            return $query->whereIn('tags.id', $tagIds);
        })->count();

        return [
            'accesslogs'      => AccessLog::where('user_id', $id)->count(),
            'games'           => Game::where('player1_id', $id)->orWhere('player2_id', $id)->count(),
            'quizTeamMember'    => $quizTeamMemberCount,
            'quizTeams'          => QuizTeam::where('owner_id', $id)->count(),
            'suggestions'     => SuggestedQuestion::where('user_id', $id)->count(),
            'tag_member'      => $tagMembers,
            'tags'            => Tag::where('creator_id', $id)->count(),
            'testSubmissions' => TestSubmission::where('user_id', $id)->count(),
            'voucherCodes'    => VoucherCode::where('user_id', $id)->count(),
            'WebinarAdditionalUsers'    => WebinarAdditionalUser::where('user_id', $id)->count(),
        ];
    }
}
