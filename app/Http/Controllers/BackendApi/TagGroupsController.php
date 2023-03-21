<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\TagGroup;
use DB;
use Illuminate\Http\Request;
use Response;

class TagGroupsController extends Controller
{
    protected $validationRules = [
        'name' => 'required',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:tag_groups,tags-edit');
    }

    /**
     * Returns all tags.
     */
    public function findTaggroups()
    {
        $groups = TagGroup::ofApp(appId())->get();

        return Response::json([
            'success' => true,
            'data' => $groups,
        ]);
    }

    /**
     *  Creates a new tag group.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validationRules);

        $tagGroup = new TagGroup();
        $tagGroup->name = $request->input('name');
        $tagGroup->app_id = appId();
        $tagGroup->can_have_duplicates = false;
        $tagGroup->show_highscore_tag = (bool) $request->input('show_highscore_tag');
        $tagGroup->signup_selectable = (bool) $request->input('signup_selectable');
        $tagGroup->signup_required = (bool) $request->input('signup_required');
        $tagGroup->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse+
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validationRules);

        $tagGroup = TagGroup::ofApp(appId())->where('id', $id)->first();
        if (! $tagGroup) {
            return Response::json([
                'success' => false,
                'error' => 'Die Taggruppe konnte nicht gefunden werden',
            ]);
        }

        $tagGroup->name = $request->input('name');
        $tagGroup->signup_selectable = (bool) $request->input('signup_selectable');
        $tagGroup->signup_required = (bool) $request->input('signup_required');
        $tagGroup->show_highscore_tag = (bool) $request->input('show_highscore_tag');
        $tagGroup->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \Throwable
     */
    public function remove($id)
    {
        $tagGroup = TagGroup::ofApp(appId())->where('id', $id)->first();
        if (! $tagGroup) {
            return Response::json([
                'success' => false,
                'error' => 'Die Taggruppe konnte nicht gefunden werden',
            ]);
        }

        DB::transaction(function () use ($tagGroup) {
            $tagGroup
                ->tags()
                ->update(['tag_group_id' => 0]);
            $tagGroup->delete();
        });

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Returns the tag groups of this app.
     *
     * @throws \Exception
     */
    public function getTagGroups()
    {
        $tagGroups = TagGroup::ofApp(appId())
            ->get()
            ->transform(function ($tagGroup) {
                return [
                    'id' => $tagGroup->id,
                    'name' => $tagGroup->name,
                ];
            });

        return Response::json([
            'tagGroups' => $tagGroups,
        ]);
    }
}
