<?php

namespace App\Console\Commands;

use App\Models\AccessLog;
use App\Models\AnalyticsEvent;
use App\Models\DirectMessage;
use App\Models\Game;
use App\Models\LearningMaterial;
use App\Models\Question;
use App\Models\User;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ShowGlobalStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'global:statistics {dateFrom} {dateTo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches some general statistics across all apps for the given timerange (YYYY-MM-DD)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $from = Carbon::parse($this->argument('dateFrom'));
        $to = Carbon::parse($this->argument('dateTo'))->endOfDay();
        $this->info('Globale Statistiken für den Zeitraum bis ' . $to);
        $gameCount = $this->getGameCount($to);
        $botGameCount = $this->getBotGameCount($to);
        $humanGameCount = $gameCount - $botGameCount;
        $this->line($humanGameCount . ' Quiz-Battles gegen Menschen');
        $this->line($botGameCount . ' Quiz-Battles gegen Bots');
        $this->line($this->getUsersHavingQuizGames($to) . ' Benutzer haben mindestens ein Quiz-Battle gespielt');
        $this->line($this->getUsers($to) . ' aktive Benutzer sind angemeldet');
        $this->line($this->getUsersHavingViewEvents($from, $to) . ' Benutzer haben seit ' . $from . ' die App aufgerufen');
        $this->line($this->getNewUsers($from, $to) . ' Benutzer haben sich seit ' . $from . ' neu angemeldet');
        $this->line('(' . $this->getNewUserAccessLogs($from, $to) . ' Einträge für ACTION_USER_SIGNUP im Access Log)');
        $this->line($this->getLearningMaterials($to) . ' Mediathek-Inhalte');
        $this->line($this->getQuestions($to) . ' Quiz-Battle-Fragen');
        $this->line($this->getDirectMessages($to) . ' Direktnachrichten');
    }

    private function getGameCount($to)
    {
        return Game::where('created_at', '<=', $to)->count();
    }

    private function getBotGameCount($to)
    {
        $botUserIds = User::withoutGlobalScopes()->where('is_bot', '>', 0)->pluck('id');
        // bots can only ever be player2
        return Game::where('created_at', '<=', $to)->whereIn('player2_id', $botUserIds)->count();
    }

    private function getUsersHavingQuizGames($to)
    {
        // SELECT count(DISTINCT users.id) as count FROM users INNER JOIN games ON users.id = games.player1_id OR users.id = games.player2_id WHERE games.created_at < {$to} AND games.status = 0
        return DB::table('users')
            ->selectRaw('count(DISTINCT users.id) AS count')
            ->join('games', function ($join) {
                $join->on('users.id', '=', 'games.player1_id')
                    ->orOn('users.id', '=', 'games.player2_id');
            })
            ->where('games.created_at', '<', $to)
            ->where('games.status', Game::STATUS_FINISHED)
            ->where('users.is_bot', 0)
            ->where('users.is_dummy', 0)
            ->where('users.is_api_user', 0)
            ->first()
            ->count;
    }

    private function getUsers($to)
    {
        return User::where('created_at', '<=', $to)
            ->showInLists()
            ->where('active', 1)
            ->whereNull('deleted_at')
            ->count();
    }

    private function getNewUsers($from, $to)
    {
        return User::where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->showInLists()
            ->count();
    }

    private function getNewUserAccessLogs($from, $to)
    {
        return AccessLog::where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->where('action', AccessLog::ACTION_USER_SIGNUP)
            ->count();
    }

    private function getLearningMaterials($to)
    {
        return LearningMaterial::where('created_at', '<=', $to)
            ->count();
    }

    private function getQuestions($to)
    {
        return Question::where('created_at', '<=', $to)
            ->count();
    }

    private function getDirectMessages($to)
    {
        return DirectMessage::where('created_at', '<=', $to)
            ->count();
    }

    private function getUsersHavingViewEvents($from, $to)
    {
        return DB::table('analytics_events')
            ->selectRaw('count(DISTINCT user_id) AS count')
            ->whereNotNull('user_id')
            ->whereIn('type', array_values(AnalyticsEvent::VIEW_EVENT_MAPPING))
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->first()
            ->count;
    }
}
