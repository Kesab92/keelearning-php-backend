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

class StatsQuizCSVController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_quiz_challenge,questions-stats');
        $this->personalDataRightsMiddleware('users');
    }

    /**
     * Download the player stats.
     *
     * @param StatsEngine $stats
     * @param PermissionEngine $permissionEngine
     * @return mixed
     */
    public function players(StatsEngine $stats, PermissionEngine $permissionEngine)
    {
        $app = App::find(appId());
        $user = Auth::user();
        $players = $stats->getQuizPlayersList();
        $players = $permissionEngine->filterPlayerStatsByTag($user, $players);
        $tags = $permissionEngine->getAvailableTags(appId(), $user);

        $data = [
            'players' => $players,
            'tags' => $tags->pluck('label', 'id'),
            'metaFields' => $app->getUserMetaDataFields($this->showPersonalData),
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
        ];

        return Excel::download(new DefaultExport($data, 'stats.quiz.csv.players'), 'quiz-battle-user-statistics.xlsx');
    }

    /**
     * Download the question stats.
     *
     * @param StatsEngine $stats
     * @param PermissionEngine $permissionEngine
     * @return mixed
     */
    public function questions(StatsEngine $stats, PermissionEngine $permissionEngine)
    {
        $questions = $stats->getQuestionList();
        $questions = $permissionEngine->filterQuestionStatsByTag(Auth::user(), $questions);

        $data = [
            'questions' => $questions,
            'appSettings' => $this->appSettings,
        ];

        return Excel::download(new DefaultExport($data, 'stats.quiz.csv.questions'), 'quiz-battle-question-statistics.xlsx');
    }

    /**
     * Download the category stats.
     *
     * @param StatsEngine $stats
     * @param PermissionEngine $permissionEngine
     * @return mixed
     */
    public function categories(StatsEngine $stats, PermissionEngine $permissionEngine)
    {
        $categories = $stats->getCategoryList();

        if ($this->appSettings->getValue('use_subcategory_system')) {
            $categories->load('categorygroup', 'categorygroup.translationRelation');
        }

        $categories = $permissionEngine->filterCategoryStatsByTag(Auth::user(), $categories);

        $data = [
            'categories' => $categories,
            'appSettings' => $this->appSettings,
        ];

        return Excel::download(new DefaultExport($data, 'stats.quiz.csv.categories'), 'quiz-battle-category-statistics.xlsx');
    }

    /**
     * Download the quiz team stats.
     *
     * @param StatsEngine $stats
     *
     * @return mixed
     */
    public function quizTeams(StatsEngine $stats)
    {
        $quizTeams = $stats->getQuizTeamList();

        $data = [
            'quizTeams' => $quizTeams,
        ];

        return Excel::download(new DefaultExport($data, 'stats.quiz.csv.quiz-teams'), 'quiz-battle-quiz-teams-statistics.xlsx');
    }
}
