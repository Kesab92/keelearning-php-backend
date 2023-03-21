<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Competition;
use App\Models\QuizTeam;
use App\Models\Tag;
use App\Services\AppSettings;
use App\Services\CompetitionEngine;
use App\Services\ImageUploader;
use App\Services\LikesEngine;
use App\Services\PermissionEngine;
use App\Stats\PlayerCorrectAnswersByCategoryBetweenDates;
use App\Stats\PlayerGameWinsBetweenDates;
use App\Traits\PersonalData;
use Auth;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Redirect;
use Session;
use Storage;
use Validator;
use View;

class CompetitionsController extends Controller
{
    use PersonalData;

    public function __construct(AppSettings $settings)
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:competitions,competitions-edit');
        $this->personalDataRightsMiddleware('competitions');
        View::share('activeNav', 'competitions');
    }

    /**
     * Displays a list of competitions.
     *
     * @return mixed
     * @throws \Exception
     */
    public function index(LikesEngine $likesEngine)
    {
        $competitions = Competition::where('app_id', appId())
            ->with(['tags', 'category.translationRelation'])
            ->where(function ($query) {
                return $query
                    ->where('app_id', appId())
                    ->tagRights();
            })
            ->orWhere(function ($query) {
                return $query
                    ->where('app_id', appId())
                    ->where('quiz_team_id', '>', 0);
            })
            ->get();

        $quizTeams = QuizTeam::where('app_id', appId())->get();
        $categories = Category
            ::with('translationRelation')
            ->where('app_id', appId())
            ->get()
            ->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE);

        $tags = Tag::rights(Auth::user())
            ->where('app_id', appId())
            ->get()
            ->sortBy('label', SORT_NATURAL|SORT_FLAG_CASE);

        $likesCounts = $likesEngine->getLikesCounts($competitions);

        return view('competitions.main', [
            'competitions' => $competitions,
            'categories' => $categories,
            'tags' => $tags,
            'likesCounts' => $likesCounts,
        ]);
    }

    /**
     * Creates a new competitions.
     *
     * @param Request $request
     *
     * @param PermissionEngine $permissionEngine
     * @return mixed
     * @throws \Exception
     */
    public function create(Request $request, PermissionEngine $permissionEngine)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required',
            'duration' => 'required',
            'title' => 'required',
            'start_at' => 'required',
            'description' => 'required',
            'tags' => 'required',
        ]);

        if ($validator->fails()) {
            Session::flash('error-message', 'Es gab einen Fehler. Bitte laden Sie die Seite neu und versuchen Sie es erneut');

            return redirect()->back();
        }

        $startAt = Carbon::createFromFormat('d.m.Y', $request->get('start_at'));
        $startAt->minute = 0;
        $startAt->second = 0;
        $startAt->hour = 0;

        $competition = new Competition();
        $competition->app_id = appId();
        $competition->title = $request->input('title');
        $competition->category_id = $request->get('category') == 'null' ? null : $request->get('category');
        $competition->quiz_team_id = '';
        $competition->duration = $request->get('duration');
        $competition->start_at = $startAt;
        $competition->description = $request->input('description');
        $competition->save();

        $permissionEngine->syncTags(
            $competition,
            $request->input('tags'),
            Auth::user()->tagRightsRelation->pluck('id'));

        Session::flash('success-message', 'Das Gewinnspiel wurde erfolgreich erstellt');

        return Redirect::to('/competitions');
    }

    /**
     * Shows the details view.
     *
     * @param $id
     *
     * @param Request $request
     * @param PermissionEngine $permissionEngine
     * @param CompetitionEngine $competitionEngine
     * @return View
     * @throws \Exception
     */
    public function details($id, Request $request, PermissionEngine $permissionEngine, CompetitionEngine $competitionEngine)
    {
        /** @var Competition $competition */
        $competition = $this->getCompetition($id, $permissionEngine);
        $tagIds = $request->get('tagIds', []);
        $tags = Tag::rights(Auth::user())
            ->where('app_id', appId())
            ->get();
        $members = $competitionEngine->getMemberStats($competition, $this->showPersonalData ? $tagIds : null);

        if ($tagIds) {
            $tagIds = explode(',', $tagIds);
        }

        return view('competitions.edit', [
            'competition' => $competition,
            'members' => $members,
            'tags' => $tags,
            'selectedTags' => $tagIds,
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
        ]);
    }

    /**
     * Shows the details view.
     *
     * @param $id
     *
     * @param Request $request
     * @param AppSettings $settings
     * @param PermissionEngine $permissionEngine
     * @param CompetitionEngine $competitionEngine
     * @return \Maatwebsite\Excel\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function download($id, Request $request, AppSettings $settings, PermissionEngine $permissionEngine, CompetitionEngine $competitionEngine)
    {
        /** @var Competition $competition */
        $competition = $this->getCompetition($id, $permissionEngine);
        $tagIds = $request->get('tagIds', []);
        $members = $competitionEngine->getMemberStats($competition, $this->showPersonalData ? $tagIds : null);
        $tagGroups = $settings->getApp()->tagGroups;
        $tags = $settings->getApp()->tags;
        $filename = 'competition-export-'.Str::slug($competition->title).'-'.Carbon::now()->format('d.m.Y-H:i').'.xlsx';

        $data = [
            'competition' => $competition,
            'members' => $members,
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
            'tagGroups' => $tagGroups,
            'tags' => $tags,
        ];

        return Excel::download(new DefaultExport($data, 'competitions.csv.download'), $filename);
    }

    /**
     * Refreshes the statistics for a competition.
     *
     * @param $id
     *
     * @param PermissionEngine $permissionEngine
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function refresh($id, PermissionEngine $permissionEngine)
    {
        $competition = $this->getCompetition($id, $permissionEngine);
        $members = $competition->members();
        $members->map(function ($user) use ($competition) {
            $user->stats = array_merge(['answersCorrect' => 0], ['wins']);

            if ($competition->hasStartDate()) {
                if ($competition->category_id === null) {
                    $user->stats = [
                            'answersCorrect' => (new PlayerCorrectAnswersByCategoryBetweenDates($user->id, $competition->category_id, $competition->start_at, $competition->getEndDate()))->clearCache(),
                            'wins' => (new PlayerGameWinsBetweenDates($user->id, $competition->start_at, $competition->getEndDate()))->clearCache(),
                        ];
                } else {
                    $user->stats = [
                        'answersCorrect' => (new PlayerCorrectAnswersByCategoryBetweenDates($user->id, $competition->category_id, $competition->start_at, $competition->getEndDate()))->clearCache(),
                    ];
                }
            }
        });
        Session::flash('success-message', 'Die Statistiken für das Gewinnspiel wurden neu berechnet.');

        return Redirect::to('/competitions?edit='.$competition->id);
    }

    /**
     * @param $id
     *
     * @param PermissionEngine $permissionEngine
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function harddelete($id, PermissionEngine $permissionEngine)
    {
        /** @var Competition $competition */
        $competition = $this->getCompetition($id, $permissionEngine);

        $result = $competition->safeRemove();
        if ($result->success === true) {
            Session::flash('success-message', 'Gewinnspiel wurde erfolgreich gelöscht');

            return redirect()->to('/competitions');
        } else {
            $result->flashMessages('error-message', 'Gewinnspiel kann nicht gelöscht werden, da folgende Abhängigkeiten bestehen:<br>');

            return redirect()->to('/competitions');
        }
    }

    /**
     * Competition start.
     * @param Request $request
     * @param $id
     * @param PermissionEngine $permissionEngine
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id, PermissionEngine $permissionEngine)
    {
        $this->validate($request, [
            'description' => 'required',
        ]);

        $competition = $this->getCompetition($id, $permissionEngine);
        if ($request->input('start_at')) {
            $startAt = Carbon::createFromFormat('d.m.Y', $request->get('start_at'));
            $startAt->minute = 0;
            $startAt->second = 0;
            $startAt->hour = 0;
            $competition->start_at = $startAt;
        }

        $competition->description = $request->input('description');
        $competition->save();

        return \Response::json([
            'success' => true,
        ]);
    }

    /**
     * Retrieves competition by given id.
     * @param $id
     * @param PermissionEngine $permissionEngine
     * @return Competition|Competition[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    private function getCompetition($id, PermissionEngine $permissionEngine)
    {
        $competition = Competition::findOrFail($id);
        if (! $competition) {
            app()->abort(404);
        }

        if ($competition->app_id != appId()) {
            app()->abort(403);
        }

        // Check tags
        if (! $competition->quiz_team_id) {
            $allowedTags = Auth::user()->tagRightsRelation()->pluck('tag_id');
            if ($allowedTags->count() > 0) {
                if (! $permissionEngine->isAllowedToUse($allowedTags, $competition->tags->pluck('id'))) {
                    app()->abort(403);
                }
            }
        }

        return $competition;
    }

    /**
     * Removes an existing cover image.
     * @param $id
     * @param ImageUploader $imageUploader
     * @throws \Exception
     */
    public function removeCoverImage($id, ImageUploader $imageUploader)
    {
        $competition = Competition::findOrFail($id);
        if ($competition->app_id !== appId()) {
            app()->abort(403);
        }
        $imageUploader->removeCoverImage($competition);
    }

    /**
     * @param $id
     * @param Request $request
     * @param ImageUploader $imageUploader
     * @throws \Exception
     */
    public function uploadCoverImage($id, Request $request, ImageUploader $imageUploader)
    {
        $competition = Competition::findOrFail($id);
        if ($competition->app_id !== appId()) {
            app()->abort(403);
        }

        $file = $request->file('file');
        if (! $imageUploader->validate($file)) {
            Session::flash('error-message', 'Dieses Dateiformat wird leider nicht unterstützt.');
            app()->abort(403);
        }

        $imageUploader->removeCoverImage($competition);
        if (! $imagePath = $imageUploader->upload($file)) {
            app()->abort(400);
        }

        $competition->cover_image = $imagePath;
        $competition->cover_image_url = Storage::url($imagePath);
        $competition->save();
    }
}
