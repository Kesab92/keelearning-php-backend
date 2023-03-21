<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Like;
use App\Models\Tag;
use App\Services\Access\CompetitionAccess;
use App\Services\CommentEngine;
use App\Services\LikesEngine;
use App\Stats\PlayerCorrectAnswersByCategoryBetweenDates;
use Carbon\Carbon;
use Response;

class CompetitionsController extends Controller
{
    /**
     * Returns a list of all active competitions.
     *
     * @param LikesEngine $likesEngine
     * @param CommentEngine $commentEngine
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(LikesEngine $likesEngine, CommentEngine $commentEngine)
    {
        $user = user();
        // Competitions depending on the user's quiz team membership
        $quizTeams = $user->quizTeams()->pluck('quiz_teams.id');
        $competitions = Competition::whereIn('quiz_team_id', $quizTeams)
            ->whereRaw('start_at <= NOW()')
            ->whereRaw('DATE_ADD(start_at,INTERVAL duration+3 DAY) >= NOW()')
            ->get();

        // Competitions depending on the user's tags
        $tmpCompetitions = collect();
        /** @var Tag $tag */
        foreach ($user->tags as $tag) {
            $results = $tag->competitions()
                ->whereRaw('start_at <= NOW()')
                ->whereRaw('DATE_ADD(start_at,INTERVAL duration+3 DAY) >= NOW()')
                ->get();
            $tmpCompetitions = $tmpCompetitions->merge($results);
        }

        // Merge both results and take care of multiple entries
        $competitions = $competitions->merge($tmpCompetitions);
        $competitions = $competitions->unique();

        $likesCounts = $likesEngine->getLikesCounts($competitions);
        $userLikes = $likesEngine->getUserLikes($competitions, $user);

        $commentsCount = $commentEngine->getCommentsCount($competitions, $user);

        $competitions = $competitions->map(function ($competition) use ($commentsCount, $likesCounts, $userLikes) {
            return $this->sanitizeCompetition($competition, $likesCounts, $userLikes, $commentsCount);
        });

        return Response::json($competitions);
    }

    /**
     * Check if a user has all requirements to join a competition.
     * @return \Illuminate\Http\JsonResponse
     */
    public function hasRequiredCredentialsForCompetitions()
    {
        $user = user();

        return Response::json([
            'temporaryAccount' => $user->isTmpAccount(),
            'maillessAccount' => $user->isMaillessAccount(),
            'hasRequiredUserCredentials' => ! empty($user->firstname) && ! empty($user->lastname),
        ]);
    }

    /**
     * Returns the competition by given id.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, LikesEngine $likesEngine)
    {
        $user = user();

        /** @var Competition $competition */
        $competition = Competition::where('id', $id)
            ->where('app_id', $user->app_id)
            ->firstOrFail();

        $competitionAccess = new CompetitionAccess();
        if (! $competitionAccess->hasAccess($user, $competition)) {
            app()->abort(403);
        }

        $likesCounts = $likesEngine->getLikesCounts(collect([$competition]));

        return Response::json($this->sanitizeCompetition($competition, $likesCounts));
    }

    private function sanitizeCompetition(Competition $competition, $likesCounts, $userLikes = null, $commentsCount = null)
    {
        $correct = (new PlayerCorrectAnswersByCategoryBetweenDates(
            user()->id,
            $competition->category_id,
            $competition->start_at,
            $competition->getEndDate()
        ))->clearCache()->fetch();
        $data = [
            'category' => $competition->getCategoryName(),
            'category_id' => $competition->category_id,
            'correct' => $correct,
            'cover_image_url' => $competition->cover_image_url,
            'cover_image' => $competition->cover_image, // TODO: legacy
            'created_at' => $competition->created_at->toDateTimeString(),
            'description' => $competition->description,
            'end' => $competition->getEndDate()->toDateTimeString(),
            'hasEnded' => $competition->getEndDate()->gt(Carbon::now()) ? 0 : 1,
            'id' => $competition->id,
            'start' => $competition->start_at->toDateTimeString(),
            'title' => $competition->title,
            'likes_count' => $likesCounts->get($competition->id, 0),
        ];
        if ($userLikes !== null) {
            $data['likes_it'] = $userLikes->contains($competition->id);
        }
        if ($commentsCount !== null) {
            $data['comment_count'] = $commentsCount->get($competition->id, 0);
        }

        return $data;
    }
}
