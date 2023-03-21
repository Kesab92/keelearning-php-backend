<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Services\PermissionEngine;
use App\Services\StatsEngine;
use App\Traits\PersonalData;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class StatsTrainingCSVController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_training,questions-stats');
        $this->personalDataRightsMiddleware('users');
    }

    protected $fileExtension = 'xlsx';

    /**
     * Download the player stats.
     *
     * @param StatsEngine $stats
     *
     * @param PermissionEngine $permissionEngine
     * @return mixed
     * @throws \Exception
     */
    public function players(StatsEngine $stats, PermissionEngine $permissionEngine)
    {
        $players = $stats->getTrainingPlayersList();
        $players = $permissionEngine->filterPlayerStatsByTag(Auth::user(), $players);

        $data = [
            'showPersonalData' => $this->showPersonalData,
            'showEmails' => $this->showEmails,
            'showIp' => $this->appSettings->getValue('save_user_ip_info') && $this->showPersonalData,
            'boxCount' => $this->appSettings->getApp()->usePowerLearning() ? 4 : 5,
            'players' => $players,
        ];

        $fileName = 'statistiken-benutzer-training';
        if(App::find(appId())->usePowerLearning()) {
            $fileName = 'statistiken-benutzer-powerlearning';
        }

        return Excel::download(new DefaultExport($data, 'stats.training.csv.players'), $fileName . '.xlsx');
    }
}
