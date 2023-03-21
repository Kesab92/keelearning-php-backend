<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\QuizTeam\QuizTeamStoreRequest;
use App\Http\Requests\BackendApi\QuizTeam\QuizTeamUpdateRequest;
use App\Models\QuizTeam;
use App\Models\User;
use App\Services\QuizTeamEngine;
use App\Traits\PersonalData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class QuizTeamsController extends Controller {
    use PersonalData;

    const STATE_NOT_SIMILAR = 0;
    const STATE_EQUAL = 1;
    const STATE_SIMILAR = 2;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:quiz,quizteams-personaldata');
        $this->personalDataRightsMiddleware('quizteams');
    }

    /**
     * Returns quiz teams data
     *
     * @param Request $request
     * @param QuizTeamEngine $quizTeamEngine
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, QuizTeamEngine $quizTeamEngine):JsonResponse
    {
        $search = $request->input('search');

        $quizTeamQuery = $quizTeamEngine->quizTeamsFilterQuery(appId(), $search);
        $quizTeamQuery->with('members');
        $quizTeams = $quizTeamQuery->get();

        return response()->json([
            'count' => $quizTeams->count(),
            'quizTeams' => $quizTeams,
        ]);
    }

    /**
     * Stores the quiz team
     *
     * @param QuizTeamStoreRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(QuizTeamStoreRequest $request): JsonResponse
    {
        $quizTeam = new QuizTeam();
        $quizTeam->app_id = appId();
        $quizTeam->owner_id = Auth::user()->id;
        $quizTeam->name = $request->input('name');
        $quizTeam->save();

        $quizTeam->members()->sync([Auth::user()->id]);
        $quizTeam->refresh();

        return Response::json([
            'quizTeam' => $this->getQuizTeamResponse($quizTeam),
        ]);
    }

    /**
     * Returns the quiz team using JSON
     *
     * @param int $quizTeamId
     * @return JsonResponse
     */
    public function show(int $quizTeamId): JsonResponse
    {
        $quizTeam = $this->getQuizTeam($quizTeamId);
        return Response::json([
            'quizTeam' => $this->getQuizTeamResponse($quizTeam),
        ]);
    }

    /**
     * Updates the quiz team
     *
     * @param int $quizTeamId
     * @param QuizTeamUpdateRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(int $quizTeamId, QuizTeamUpdateRequest $request):JsonResponse {
        $quizTeam = $this->getQuizTeam($quizTeamId);

        DB::transaction(function() use ($quizTeam, $request) {
            $basicFields = [
                'name',
                'owner_id',
            ];

            foreach ($basicFields as $field) {
                if ($request->has($field)) {
                    $value = $request->input($field, null);
                    $quizTeam->setAttribute($field, $value);
                }
            }

            $quizTeam->save();

            if($request->has('members')) {
                $managersIds = User::ofApp(appId())
                    ->showInLists()
                    ->whereIn('id', $request->input('members', []))
                    ->get()
                    ->pluck('id');

                $quizTeam->members()->sync($managersIds);
                $quizTeam->refresh();
            }
        });

        return Response::json([
            'quizTeam' => $this->getQuizTeamResponse($quizTeam),
        ]);
    }

    /**
     * Returns dependencies and blockers
     *
     * @param int $quizTeamId
     * @return JsonResponse
     */
    public function deleteInformation(int $quizTeamId):JsonResponse
    {
        $quizTeam = $this->getQuizTeam($quizTeamId);
        return Response::json([
            'dependencies' => $quizTeam->safeRemoveDependees(),
            'blockers' => $quizTeam->getBlockingDependees(),
        ]);
    }

    /**
     * Deletes the quiz eam
     *
     * @param int $quizTeamId
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete(int $quizTeamId):JsonResponse {
        $quizTeam = $this->getQuizTeam($quizTeamId);

        $result = $quizTeam->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Returns quiz teams for lists
     *
     * @param QuizTeamEngine $quizTeamEngine
     * @return JsonResponse
     * @throws \Exception
     */
    public function list(QuizTeamEngine $quizTeamEngine):JsonResponse
    {
        $quizTeamQuery = $quizTeamEngine->quizTeamsFilterQuery(appId());
        $quizTeams = $quizTeamQuery->get()->map->only([
            'id',
            'name',
        ]);

        return response()->json([
            'quizTeams' => $quizTeams,
        ]);
    }

    /**
     * Checks if there are similar quiz team names
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validQuizTeamName(Request $request): JsonResponse
    {
        // Get all quiz teams from this app
        $quizTeams = QuizTeam::whereAppId(appId())
            ->get();

        // Check for each quiz team how similar they are compared to the query
        foreach ($quizTeams as $quizTeam) {
            if ($request->input('q') == $quizTeam->name) {
                return response()->json(['state' => self::STATE_EQUAL]);
            }
            // If at least two strings are too equal, return true
            if (levenshtein($request->input('q'), $quizTeam->name) <= 2) {
                return response()->json(['state' => self::STATE_SIMILAR]);
            }
        }

        return response()->json(['state' => self::STATE_NOT_SIMILAR]);
    }

    private function getQuizTeam(int $quizTeamId):QuizTeam {
        return QuizTeam
            ::ofApp(appId())
            ->with('members')
            ->findOrFail($quizTeamId);
    }

    private function getQuizTeamResponse(QuizTeam $quizTeam): array
    {
        $members = $quizTeam->members->map(function ($member) {
            $email = $this->showEmails ? $member->email : '';
            $fullName = $this->showPersonalData ? $member->getFullName() : $member->username;

            return collect([
                'id' => $member->id,
                'email' => $email,
                'fullName' => $fullName,
            ]);
        });

        $response = $quizTeam->toArray();
        $response['members'] = $members;

        return $response;
    }
}
