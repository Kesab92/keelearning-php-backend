<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Services\PermissionEngine;
use App\Services\StatsEngine;
use App\Traits\PersonalData;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class StatsQuizController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:stats_quiz_challenge,questions-stats');
        $this->personalDataRightsMiddleware('users');
    }

    /**
     * Shows the player stats.
     *
     * @param Request $request
     * @param StatsEngine $stats
     * @param PermissionEngine $permissionEngine
     * @return mixed
     * @throws \Exception
     */
    public function players(Request $request, StatsEngine $stats, PermissionEngine $permissionEngine)
    {
        $tags = $request->get('tags', []);
        $page = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $perPage = min($request->input('rowsPerPage', 50), 200);
        $sortBy = $request->input('sortBy', 'id');
        $sortDescending = $request->input('descending', $this->getDefaultSortDesc($sortBy)) == 'true';

        // The php 7.2 garbage collector tries to garbage collect 40 times while rendering the view, so we tell him not to
        gc_disable();
        $players = $stats->getQuizPlayersList();

        if (count($tags) > 0) {
            $players = $players->filter(function ($user) use ($tags) {
                $userTags = explode(',', $user->stats['tags']);
                $userTags = array_filter($userTags, 'strlen');

                // it checks if the user doesn't contain any tag
                if(in_array(-1, $tags) && count($userTags) === 0) {
                    return true;
                }
                return $userTags && array_intersect($tags, $userTags);
            });
        }

        $players = $permissionEngine->filterPlayerStatsByTag(Auth::user(), $players);

        if ($sortBy) {
            if (Str::startsWith($sortBy, 'category-')) {
                $sortCategory = explode('-', $sortBy)[1];
                $players = $players->sortBy(function ($user) use ($sortCategory) {
                    return $user->stats['categories'][$sortCategory]['answersCorrectPercentage'];
                }, SORT_REGULAR, $sortDescending);
            } else {
                switch ($sortBy) {
                    case 'id':
                    case 'username':
                    case 'firstname':
                    case 'lastname':
                        $players = $players->sortBy(function ($user) use ($sortBy) {
                            return $user[$sortBy];
                        }, SORT_REGULAR, $sortDescending);
                        break;
                    default:
                        $players = $players->sortBy(function ($user) use ($sortBy) {
                            $sortBy = str_replace('stats.', '', $sortBy);
                            return $user->stats[$sortBy];
                        }, SORT_REGULAR, $sortDescending);
                        break;
                }
            }
        }

        $playerCount = $players->count();

        $players = $this->formatPlayers($players);

        $players = new LengthAwarePaginator($players->forPage($page, $perPage), $players->count(), 300, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        gc_enable();

        return \Response::json([
            'count' => $playerCount,
            'players' => $players->values(),
            'headers' => $this->getHeadersForPlayerTab($players),
            'metaFields' => App::find(appId())->getUserMetaDataFields($this->showPersonalData),
        ]);
    }

    /**
     * Shows the question stats.
     *
     * @param StatsEngine $stats
     * @param PermissionEngine $permissionEngine
     * @return mixed
     */
    public function questions(Request $request, StatsEngine $stats, PermissionEngine $permissionEngine)
    {
        $page = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $perPage = min($request->input('rowsPerPage', 50), 200);
        $sortBy = $request->input('sortBy', 'id');
        $sortDescending = $request->input('descending', $this->getDefaultSortDesc($sortBy)) == 'true';

        $questions = $stats->getQuestionList();
        $questions = $permissionEngine->filterQuestionStatsByTag(Auth::user(), $questions);

        if ($sortBy) {
            switch ($sortBy) {
                case 'id':
                case 'title':
                case 'difficulty':
                    $questions = $questions->sortBy(function ($question) use ($sortBy) {
                        return $question[$sortBy];
                    }, SORT_REGULAR, $sortDescending);
                    break;
                default:
                    $questions = $questions->sortBy(function ($question) use ($sortBy) {
                        $sortBy = str_replace('stats.', '', $sortBy);
                        return $question->stats[$sortBy];
                    }, SORT_REGULAR, $sortDescending);
                    break;
            }

        }

        $questionCount = $questions->count();

        $questions = new LengthAwarePaginator($questions->forPage($page, $perPage), $questions->count(), 300, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return \Response::json([
            'count' => $questionCount,
            'questions' => $questions->values(),
            'headers' => $this->getHeadersForQuestionTab(),
        ]);
    }

    /**
     * Shows the category stats.
     *
     * @param StatsEngine $stats
     * @param PermissionEngine $permissionEngine
     * @return mixed
     */
    public function categories(Request $request, StatsEngine $stats, PermissionEngine $permissionEngine)
    {
        $page = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $perPage = min($request->input('rowsPerPage', 50), 200);
        $sortBy = $request->input('sortBy', 'id');
        $sortDescending = $request->input('descending', $this->getDefaultSortDesc($sortBy)) == 'true';

        $categories = $stats->getCategoryList();

        if ($this->appSettings->getValue('use_subcategory_system')) {
            $categories->load('categorygroup', 'categorygroup.translationRelation');
        }

        $categories = $permissionEngine->filterCategoryStatsByTag(Auth::user(), $categories);

        if ($sortBy) {
            switch ($sortBy) {
                case 'id':
                case 'name':
                case 'active':
                    $categories = $categories->sortBy(function ($category) use ($sortBy) {
                        return $category[$sortBy];
                    }, SORT_REGULAR, $sortDescending);
                    break;
                default:
                    $categories = $categories->sortBy(function ($category) use ($sortBy) {
                        $sortBy = str_replace('stats.', '', $sortBy);
                        return $category->stats[$sortBy];
                    }, SORT_REGULAR, $sortDescending);
                    break;
            }
        }

        $categoryCount = $categories->count();

        $categories = new LengthAwarePaginator($categories->forPage($page, $perPage), $categories->count(), 300, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return \Response::json([
            'count' => $categoryCount,
            'categories' => $categories->values(),
            'headers' => $this->getHeadersForCategoryTab(),
        ]);
    }

    /**
     * Shows the quiz team stats.
     *
     * @return mixed
     */
    public function quizTeams(Request $request, StatsEngine $stats)
    {
        if (! Auth::user()->isFullAdmin()) {
            app()->abort(404);
        }

        $page = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $perPage = min($request->input('rowsPerPage', 50), 200);
        $sortBy = $request->input('sortBy', 'id');
        $sortDescending = $request->input('descending', $this->getDefaultSortDesc($sortBy)) == 'true';

        $quizTeams = $stats->getQuizTeamList();

        if ($sortBy) {
            switch ($sortBy) {
                case 'id':
                case 'name':
                case 'member_count':
                case 'created_at':
                    $quizTeams = $quizTeams->sortBy(function ($quizTeam) use ($sortBy) {
                        return $quizTeam[$sortBy];
                    }, SORT_REGULAR, $sortDescending);
                    break;
                default:
                    $quizTeams = $quizTeams->sortBy(function ($quizTeam) use ($sortBy) {
                        $sortBy = str_replace('stats.', '', $sortBy);
                        return $quizTeam->stats[$sortBy];
                    }, SORT_REGULAR, $sortDescending);
                    break;
            }
        }

        $quizTeamCount = $quizTeams->count();

        $quizTeams = new LengthAwarePaginator($quizTeams->forPage($page, $perPage), $quizTeamCount, 300, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        return \Response::json([
            'count' => $quizTeamCount,
            'quizTeams' => $quizTeams->values(),
            'headers' => $this->getHeadersForQuizTeamTab(),
        ]);
    }

    private function getDefaultSortDesc($sort)
    {
        $ascSorters = ['country', 'username'];
        if (in_array($sort, $ascSorters)) {
            return 0;
        }

        return 1;
    }

    private function getHeadersForPlayerTab($players) {
        $headers = [
            [
                'text' => 'ID',
                'value' => 'id',
                'sortable' => true,
            ],
        ];

        if ($this->showPersonalData) {
            $headers[] = [
                'text' => 'Benutzer',
                'value' => 'username',
                'sortable' => true,
            ];
            $headers[] = [
                'text' => 'Vorname',
                'value' => 'firstname',
                'sortable' => true,
            ];
            $headers[] = [
                'text' => 'Nachname',
                'value' => 'lastname',
                'sortable' => true,
            ];
        }

        $headers[] = [
            'text' => 'TAGs',
            'value' => 'tags',
            'sortable' => false,
        ];
        $headers[] = [
            'text' => 'Anzahl Spiele gesamt',
            'value' => 'stats.gameCount',
            'sortable' => true,
        ];
        $headers[] = [
            'text' => 'Spiele gewonnen',
            'value' => 'stats.gameWins',
            'sortable' => true,
        ];
        $headers[] = [
            'text' => 'Spiele unentschieden',
            'value' => 'stats.gameDraws',
            'sortable' => true,
        ];
        $headers[] = [
            'text' => 'Spiele verloren',
            'value' => 'stats.gameLosses',
            'sortable' => true,
        ];
        $headers[] = [
            'text' => 'Spiele abgebrochen',
            'value' => 'stats.gameAborts',
            'sortable' => true,
        ];
        $headers[] = [
            'text' => 'Winrate Spiele',
            'value' => 'stats.gameWinPercentage',
            'sortable' => true,
        ];
        $headers[] = [
            'text' => 'Fragen richtig',
            'value' => 'stats.answersCorrectPercentage',
            'sortable' => true,
        ];
        if($players->isNotEmpty()) {
            foreach (reset($players->toArray()['data'])['stats']['categories'] as $id => $category) {
                $headers[] = [
                    'text' => $category['name'],
                    'value' => 'category-' . $id,
                    'sortable' => true,
                ];
            }
        }

        return $headers;
    }

    private function getHeadersForQuestionTab() {
        return [
            [
                'text' => 'ID',
                'value' => 'id',
                'sortable' => true,
            ],
            [
                'text' => 'Frage',
                'value' => 'title',
                'sortable' => true,
            ],
            [
                'text' => 'Richtig beantwortet',
                'value' => 'stats.correct',
                'sortable' => true,
            ],
            [
                'text' => 'Falsch beantwortet',
                'value' => 'stats.wrong',
                'sortable' => true,
            ],
            [
                'text' => 'Schwierigkeit (kleine Wert = schwierig)',
                'value' => 'difficulty',
                'sortable' => true,
            ],
        ];
    }

    private function getHeadersForCategoryTab()
    {
        $headers = [];
        if ($this->appSettings->getValue('use_subcategory_system')) {
            $headers[] = [
                'text' => 'Oberkategorie',
                'value' => 'categorygroup.name',
                'sortable' => false,
            ];
        }
        array_push(
            $headers,
            [
                'text' => 'Kategorie',
                'value' => 'name',
                'sortable' => true,
            ],
            [
                'text' => 'Richtig beantwortete Fragen',
                'value' => 'stats.correct',
                'sortable' => true,
            ],
            [
                'text' => 'Falsch beantwortete Fragen',
                'value' => 'stats.wrong',
                'sortable' => true,
            ],
            [
                'text' => 'Aktiv',
                'value' => 'active',
                'sortable' => true,
            ],
        );

        return $headers;
    }

    private function getHeadersForQuizTeamTab() {
        return [
            [
                'text' => 'Quiz-Team',
                'value' => 'name',
                'sortable' => true,
            ],
            [
                'text' => 'Richtig beantwortete Fragen',
                'value' => 'stats.answersCorrect',
                'sortable' => true,
            ],
            [
                'text' => 'Gewonnene Spiele',
                'value' => 'stats.gameWinPercentage',
                'sortable' => true,
            ],
            [
                'text' => 'Mitglieder',
                'value' => 'member_count',
                'sortable' => true,
            ],
            [
                'text' => 'Erstellt am',
                'value' => 'created_at',
                'sortable' => true,
            ],
        ];
    }

    private function formatPlayers($players) {
        $metafields = App::findOrFail(appId())->getUserMetaDataFields($this->showPersonalData);
        return $players->map(function ($player) use ($metafields) {
            $playerData = [
                'id' => $player->id,
                'stats' => $player->stats,
                'tags' => $player->tags->pluck('id'),
            ];

            $playerData['meta'] = [];
            foreach ($metafields as $metafield => $metavalue) {
                $playerData['meta'][$metafield] = $player->getMeta($metafield) ?? '';
            }

            if($this->showEmails) {
                $playerData['email'] = $player->email;
            }
            if($this->showPersonalData) {
                $playerData['username'] = $player->username;
                $playerData['firstname'] = $player->firstname;
                $playerData['lastname'] = $player->lastname;
            }

            return $playerData;
        });
    }
}
