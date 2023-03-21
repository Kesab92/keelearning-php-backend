<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Services\WbtEngine;
use App\Traits\PersonalData;
use Auth;
use Illuminate\Http\Request;
use Response;

class WbtController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_wbt,courses-stats|learningmaterials-stats');
        $this->personalDataRightsMiddleware('learningmaterials');
    }

    /**
     * @throws \Exception
     */
    public function getEvents(Request $request, WbtEngine $wbtEngine)
    {
        $user = Auth::user();
        if ($request->input('courseId')) {
            if (!$user->hasRight('courses-stats')) {
                app()->abort(403);
            }
            $this->checkPersonalDataRights('courses', $user);
        } else {
            if (!$user->hasRight('learningmaterials-stats')) {
                app()->abort(403);
            }
        }

        $eventResponse = $wbtEngine->getEvents(
            $request->input('search'),
            $request->input('learningmaterials'),
            $request->input('courseId'),
            Auth::user(),
            $request->input('sortBy'),
            $request->input('descending', false),
            $request->input('page', 0),
            $request->input('rows', 0),
            $this->showPersonalData
        );

        return Response::json([
            'events' => $eventResponse->get('events'),
            'eventcount' => $eventResponse->get('eventcount'),
        ]);
    }
}
