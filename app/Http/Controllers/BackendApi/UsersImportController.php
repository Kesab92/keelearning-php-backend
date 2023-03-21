<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Imports\Exceptions\InvalidDataException;
use App\Imports\Exceptions\InvalidHeadersException;
use App\Imports\UsersImporter;
use App\Jobs\ImportUsers;
use App\Models\App;
use App\Models\QuizTeam;
use App\Models\Import;
use App\Models\Tag;
use App\Models\TagGroup;
use Auth;
use Illuminate\Http\Request;
use Response;

class UsersImportController extends Controller
{
    private $appSettings;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,users-edit');
        $this->middleware('auth.appsetting:import_users');
    }

    public function userImportConfiguration()
    {
        /** @var App $app */
        $app = App::find(appId());
        $app->getLanguages();

        // We want to allow the user to select a column as a tag group an we then set the tags when executing the import
        $noDuplicateTagGroups = TagGroup::where('app_id', appId())
            ->where('can_have_duplicates', 0)
            ->get()
            ->map(function ($tagGroup) {
                return [
                    'id' => $tagGroup->id,
                    'name' => $tagGroup->name,
                ];
            })
            ->toArray();

        $metaFields = collect($app->getUserMetaDataFields(true))
            ->filter(function ($metaField) {
                return (bool) $metaField['import'];
            });

        return Response::json([
            'app_id'    => $app->id,
            'languages' => $app->getLanguages(),
            'meta'      => $metaFields,
            'tagGroups' => $noDuplicateTagGroups,
        ]);
    }

    /**
     * Imports users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function collectChanges(Request $request)
    {
        $data = json_decode($request->get('data'), true);

        $tagIds = $data['tag_ids'];
        $quizTeamId = $data['quiz_team_id'];
        $compareHeader = $data['compare_header'];
        $headers = $data['headers'];
        $users = $data['users'];
        $deleteUsers = (bool) $data['delete_users'];
        $dontInviteUsers = (bool) $data['dont_invite_users'];

        $tags = null;
        if ($tagIds) {
            $tags = Tag::whereIn('id', $tagIds)->where('app_id', appId())->get();
        }
        $quizTeam = null;
        if ($quizTeamId) {
            $quizTeam = QuizTeam::where('id', $quizTeamId)->where('app_id', appId())->first();
        }

        /** @var UsersImporter $importer */
        $importer = app(UsersImporter::class);
        $error = null;
        $additionalData = [
            'tags' => $tags,
            'quizTeam' => $quizTeam,
            'appId' => appId(),
            'compareHeader' => $compareHeader,
            'deleteUsers' => $deleteUsers,
            'dontInviteUsers' => $dontInviteUsers,
            'creatorId' => Auth::user()->id
        ];
        $changes = [];
        try {
            $changes = $importer->collectChanges($additionalData, $headers, $users);
        } catch (InvalidDataException $e) {
            report($e);
            $error = 'Es wurden ungültige Daten übergeben.';
        } catch (InvalidHeadersException $e) {
            report($e);
            $error = 'Es wurden eine ungültige Spaltenzuordnung übergeben.';
        } catch (\Exception $e) {
            report($e);
            $error = $e->getMessage();
        }
        if ($error) {
            return Response::json(['errors' => [$error]], 400);
        }

        return Response::json($changes);
    }

    /**
     * Imports users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function import(Request $request)
    {
        $data = json_decode($request->get('data'), true);
        $user = Auth::user();

        $tagIds = $data['tag_ids'];
        $quizTeamId = $data['quiz_team_id'];
        $compareHeader = $data['compare_header'];
        $headers = $data['headers'];
        $users = $data['users'];
        $deleteUsers = (bool) $data['delete_users'];
        $dontInviteUsers = (bool) $data['dont_invite_users'];

        if(!$user->isFullAdmin()) {
            $userTagRights = $user->tagRightsRelation->pluck('id');
            $availableTags = $userTagRights->intersect($tagIds);
            if($availableTags->count() !== count($tagIds) || count($tagIds) == 0) {
                abort(403);
            }
        }

        $tags = null;
        if ($tagIds) {
            $tags = Tag::whereIn('id', $tagIds)->where('app_id', appId())->get();
        }
        $quizTeam = null;
        if ($quizTeamId) {
            $quizTeam = QuizTeam::where('id', $quizTeamId)->where('app_id', appId())->first();
        }

        $import = new Import();
        $import->app_id = appId();
        $import->creator_id = Auth::user()->id;
        $import->type = Import::TYPE_USERS_IMPORT;
        $import->status = Import::STATUS_INPROGRESS;
        $import->steps = 5;
        if ($deleteUsers) {
            $import->steps++;
        }
        $import->save();

        $additionalData = [
            'tags' => $tags,
            'quizTeam' => $quizTeam,
            'appId' => appId(),
            'creatorId' => Auth::user()->id,
            'compareHeader' => $compareHeader,
            'importId' => $import->id,
            'deleteUsers' => $deleteUsers,
            'dontInviteUsers' => $dontInviteUsers,
        ];

        ImportUsers::dispatch($additionalData, $headers, $users);

        return Response::json([
            'importId' => $import->id,
        ]);
    }
}
