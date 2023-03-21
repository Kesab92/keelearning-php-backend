<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Game;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use View;

class BaldingerController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $from = Carbon::parse($request->input('from', Carbon::now()->subDays(7)->format('Y-m-d')))->startOfDay();
        $until = Carbon::parse($request->input('until', Carbon::now()->format('Y-m-d')))->endOfDay();

        if($until->isBefore($from)) {
            app()->abort(400, 'Der "until" Parameter muss nach dem "from" Parameter sein.');
        }

        if($from->diffInDays($until) > 30) {
            app()->abort(400, 'Bitte maximal einen Zeitraum von 30 Tagen abfragen.');
        }

        $categoryId = $request->input('category', null);
        if(!$categoryId) {
            app()->abort(404, 'Bitte "category" Parameter angeben.');
        }
        $category = Category::find($categoryId);
        if(!$category || $category->app_id !== appId()) {
            app()->abort(404, 'Ungültiger "category" Parameter.');
        }

        $tagIds = array_filter(explode(',',$request->input('tags', '')));
        if(!$tagIds) {
            app()->abort(404, 'Ungültiger "tags" Parameter.');
        }
        $tags = Tag::whereIn('id', $tagIds)->where('app_id', appId())->get();
        if(!$tags) {
            app()->abort(404, 'Ungültiger "tags" Parameter.');
        }

        $users = User::where('app_id', appId())
            ->with('tags')
            ->where('is_dummy', false)
            ->where('is_api_user', 0)
            ->whereNull('deleted_at')
            ->whereHas('tags', function (Builder $query) use ($tags) {
                $query->whereIn('tags.id', $tags->pluck('id'));
            })
            ->get()
            ->keyBy('id');

        if(!$users) {
            app()->abort(404, 'Keine Benutzer gefunden');
        }

        /** @var Game[] $games */
        $games = Game::where('app_id', appId())
            ->with('gameRounds')
            ->whereIn('player1_id', $users->pluck('id'))
            ->whereBetween('created_at', [$from, $until])
            ->get();

        $days = [];
        $currentDay = $from->clone();

        while($currentDay->lte($until)) {
            $days[$currentDay->format('Y-m-d')] = $this->getDayData($currentDay, $games, $tags, $category, $users);
            $currentDay->addDay();
        }

        return view('stats.baldinger.stats', [
            'days' => $days,
        ]);
    }

    private function getDayData(Carbon $day, $games, $tags, $category, $users) {
        $start = $day->startOfDay()->format('Y-m-d H:i:s');
        $end = $day->endOfDay()->format('Y-m-d H:i:s');
        $games = $games->where('created_at', '<=', $end)->where('created_at', '>=', $start);
        $tagInformation = collect($tags->toArray())->keyBy('id')->toArray();

        foreach($games as $game) {
            if(!$game->gameRounds->contains('category_id', $category->id)) {
                continue;
            }
            /** @var User $user */
            $user = $users->get($game->player1_id);
            foreach($tags as $tag) {
                if($user->tags->contains('id', $tag->id)) {
                    if(!isset($tagInformation[$tag->id]['gamecount'])) {
                        $tagInformation[$tag->id]['gamecount'] = 0;
                    }
                    $tagInformation[$tag->id]['gamecount']++;

                    if(!isset($tagInformation[$tag->id]['users'])) {
                        $tagInformation[$tag->id]['users'] = [];
                    }
                    $tagInformation[$tag->id]['users'][$user->id] = true;
                }
            }
        }

        return $tagInformation;
    }
}
