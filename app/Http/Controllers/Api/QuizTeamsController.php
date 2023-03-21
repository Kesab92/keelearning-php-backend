<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\QuizTeam;
use App\Models\User;
use App\Services\StatsEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Response;

class QuizTeamsController extends Controller
{

    /**
     * Returns all quiz teams of the user.
     *
     * @return JsonResponse
     */
    public function mine()
    {
        return Response::json([
            'quizTeams' => $this->getMyQuizTeams()->values()->toArray(),
        ]);
    }

    public function show($id)
    {
        $user = user();
        $quizTeam = QuizTeam::findOrFail($id);
        if ($quizTeam->app_id !== $user->app_id) {
            return app()->abort(403);
        }
        /** @var User[]|Collection $members */
        $members = $quizTeam->members()
            ->get();

        $statsEngine = new StatsEngine(user()->app_id);
        $memberStats = (new Collection($statsEngine->getAPIPlayerList()))->whereIn('id', $members->pluck('id'))->keyBy('id');
        $quizTeamStats = (new Collection($statsEngine->getQuizTeamsApiList()))->where('id', $quizTeam->id)->first();

        $members->transform(function ($member) use ($memberStats) {
            /** @var User $member */
            $stats = [];
            if (isset($memberStats[$member->id])) {
                $memberStatsEntry = $memberStats[$member->id];
                $stats = [
                    'position' => $memberStatsEntry['position'],
                    'answers_correct' => $memberStatsEntry['answersCorrect'],
                    'game_wins' => $memberStatsEntry['gameWins'],
                ];
            }
            return [
                'id' => $member->id,
                'avatar_url' => $member->avatar_url,
                'username' => $member->getDisplayNameFrontend(),
                'displayname' => $member->displayname,
                'stats' => $stats,
            ];
        });

        return Response::json([
            'team' => $quizTeam->toArray() + [
                'members' => $members->toArray(),
                'stats' => $quizTeamStats ?? null,
            ],
        ]);
    }

    /**
     * Helper function to determine if a given quiz team name is valid.
     *
     * @param $name
     * @return APIError|bool
     */
    private function quizTeamNameIsValid($name)
    {
        if (strlen($name) <= 2) {
            return new APIError(__('errors.quiz_team_name_too_short'), 400);
        }
        // Check if the quiz team exists
        $teamExists = QuizTeam::where('app_id', user()->app_id)->where('name', $name)->count();
        if ($teamExists) {
            return new APIError(__('errors.quiz_team_name_taken'), 400);
        }

        return true;
    }

    /**
     * Checks if a given quiz team name is valid.
     *
     * @param Request $request
     * @return APIError|JsonResponse
     */
    public function checkQuizTeamName(Request $request)
    {
        $name = $request->get('name');
        $error = $this->quizTeamNameIsValid($name);
        if ($error instanceof APIError) {
            return $error;
        }

        return Response::json([]);
    }

    /**
     * Creates a new quiz team.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $name = $request->get('name');
        $error = $this->quizTeamNameIsValid($name);
        if ($error instanceof APIError) {
            return $error;
        }

        $quizTeam = $this->storeQuizTeam($name);

        return Response::json([
            'team' => [
                'id' => $quizTeam->id,
            ],
        ]);
    }

    /**
     * Adds a member to a quiz team.
     *
     * @param Request $request
     * @param         $quizTeamId
     * @return APIError|JsonResponse
     */
    public function addMember(Request $request, $quizTeamId)
    {
        $user = User::findOrFail($request->get('user_id'));
        $quizTeam = QuizTeam::find($quizTeamId);

        if ($quizTeam->owner_id != user()->id) {
            return new APIError(__('errors.not_quiz_team_owner'), 403);
        }

        if ($user->app_id != user()->app_id ||
            $user->is_dummy ||
            $user->is_api_user
        ) {
            return new APIError(__('errors.generic'));
        }

        // Check if the new user isn't already in the quiz team
        if ($quizTeam->members->contains('id', $user->id)) {
            return new APIError(__('errors.already_quiz_team_member'));
        }

        // Add the member
        $quizTeam->members()->attach($user->id);

        $statsEngine = new StatsEngine(user()->app_id);
        $stats = (new Collection($statsEngine->getAPIPlayerList()))->where('id', $user->id)->first();

        return Response::json([
            'stats' => $stats,
        ]);
    }

    /**
     * @param string $name
     * @return QuizTeam
     */
    private function storeQuizTeam(string $name):QuizTeam {
        $quizTeam = new QuizTeam();
        $quizTeam->app_id = user()->app_id;
        $quizTeam->name = $name;
        $quizTeam->owner_id = user()->id;
        $quizTeam->save();

        $quizTeam->members()->attach(user()->id);

        return $quizTeam;
    }

    /**
     * Returns the user's quiz teams
     * @return mixed
     */
    private function getMyQuizTeams()
    {
        $user = user();
        $quizTeams = QuizTeam::select(['id', 'name'])
            ->where('app_id', $user->app_id)
            ->with('members')
            ->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();

        $statsEngine = new StatsEngine($user->app_id);
        $statsForAllQuizTeams = collect($statsEngine->getQuizTeamsApiList());

        $quizTeams->transform(function ($quizTeam) use ($statsForAllQuizTeams) {
            $someMembers = $quizTeam->members->take(6)->map(function ($member) {
                /* @var User $member */
                return [
                    'id' => $member->id,
                    'username' => $member->getDisplayNameFrontend(),
                    'displayname' => $member->displayname,
                    'avatar_url' => $member->avatar_url,
                ];
            });
            $quizTeamStats = $statsForAllQuizTeams->where('id', $quizTeam->id)->first();

            if ($quizTeamStats) {
                $stats = [
                    'game_wins' => $quizTeamStats['gameWins'],
                    'answers_correct' => $quizTeamStats['answersCorrect'],
                    'position' => $quizTeamStats['position'],
                ];
            } else {
                $stats = null;
            }

            return [
                'id' => $quizTeam->id,
                'name' => $quizTeam->name,
                'some_members' => $someMembers,
                'member_count' => $quizTeam->members->count(),
                'stats' => $stats,
            ];
        });

        return $quizTeams;
    }

    /**
     * Returns all quiz teams of the user.
     *
     * @deprecated
     * @return \Illuminate\Http\JsonResponse
     */
    public function quizTeams()
    {
        $quizTeams = QuizTeam::select('id', 'name')->whereHas('members', function ($q) {
            $q->where('user_id', user()->id);
        })->get();

        return Response::json($quizTeams);
    }

    /**
     * Returns all quiz teams of the user, including relevant stats.
     *
     * @deprecated
     * @return \Illuminate\Http\JsonResponse
     */
    public function quizTeamsWithStats()
    {
        $quizTeams = QuizTeam::select('id', 'name')->whereHas('members', function ($q) {
            $q->where('user_id', user()->id);
        })->withCount('members')->get();

        $statsEngine = new StatsEngine(user()->app_id);
        $stats = (new Collection($statsEngine->getQuizTeamsApiList()))->whereIn('id', $quizTeams->pluck('id'))->pluck('position', 'id');

        return Response::json([
            'groups' => $quizTeams,
            'stats' => $stats,
        ]);
    }

    /**
     * Get a quiz team with its members.
     *
     * @deprecated
     * @param $quizTeamId
     * @return \Illuminate\Http\JsonResponse
     */
    public function quizTeam($quizTeamId)
    {
        $quizTeam = QuizTeam::findOrFail($quizTeamId);

        if ($quizTeam->app_id != user()->app_id) {
            return new APIError(__('errors.generic'));
        }

        $members = $quizTeam->members()
            ->get();

        $statsEngine = new StatsEngine(user()->app_id);
        $stats = (new Collection($statsEngine->getAPIPlayerList()))->whereIn('id', $members->pluck('id'))->keyBy('id');

        return Response::json([
            'group' => $quizTeam->toArray() + [
                    'members' => $members->toArray(),
                ],
            'stats' => $stats,
        ]);
    }

    /**
     * Creates a new quiz team.
     *
     * @deprecated
     * @param Request $request
     * @return JsonResponse
     */
    public function createAndReturnOldDataFormat(Request $request)
    {
        $name = $request->get('name');
        $error = $this->quizTeamNameIsValid($name);
        if ($error instanceof APIError) {
            return $error;
        }

        $quizTeam = $this->storeQuizTeam($name);

        return Response::json($quizTeam->toArray());
    }

    /**
     * @deprecated
     * @param Request $request
     * @param         $quizTeamId
     * @return APIError|\Illuminate\Http\JsonResponse
     */
    public function removeMember(Request $request, $quizTeamId)
    {

        /** @var QuizTeam $quizTeam */
        $quizTeam = QuizTeam::find($quizTeamId);

        if ($quizTeam->owner_id != user()->id) {
            return new APIError(__('errors.not_quiz_team_owner'), 403);
        }

        // Remove the member
        $quizTeam->members()
            ->detach($request->get('user_id'));

        return Response::json([]);
    }
    /**
     * Returns all quiz teams of the user.
     *
     * @deprecated
     * @return JsonResponse
     */
    public function mineWithOldFormat()
    {
        return Response::json([
            'teams' => $this->getMyQuizTeams()->values()->toArray(),
        ]);
    }
}
