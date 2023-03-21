<?php

namespace App\Stats;

use App\Models\LearnBoxCard;
use DB;

/**
 * Calculates the training question boxes.
 */
class PlayerQuestionBoxesByCategory extends Statistic
{
    /**
     * @var
     */
    private $playerId;
    private $categoryId;
    private static $_preloadData;

    public function __construct($playerId, $categoryId)
    {
        $this->playerId = $playerId;
        $this->categoryId = $categoryId;
    }

    public static function preload($appId)
    {
        self::$_preloadData = [];
        $data = LearnBoxCard::join('users', 'users.id', '=', 'learn_box_cards.user_id')
            ->join('questions', 'questions.id', '=', 'learn_box_cards.foreign_id')
            ->where('users.app_id', $appId)
            ->where('learn_box_cards.type', 0)
            ->groupBy('user_id', 'box', 'category_id')
            ->select(DB::RAW('COUNT(*) as count, user_id, box, category_id'))
            ->get();
        foreach ($data as $entry) {
            if (! isset(self::$_preloadData[$entry->category_id])) {
                self::$_preloadData[$entry->category_id] = [];
            }
            if (! isset(self::$_preloadData[$entry->category_id][$entry->user_id])) {
                self::$_preloadData[$entry->category_id][$entry->user_id] = [];
            }
            if (! isset(self::$_preloadData[$entry->category_id][$entry->user_id]['total'])) {
                self::$_preloadData[$entry->category_id][$entry->user_id]['total'] = 0;
            }
            self::$_preloadData[$entry->category_id][$entry->user_id]['total'] += $entry->count;
            self::$_preloadData[$entry->category_id][$entry->user_id]['box_'.($entry->box + 1)] = $entry->count;
        }
    }

    /**
     * Returns the amount of games the user played.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->categoryId][$this->playerId]) && isset(self::$_preloadData[$this->categoryId][$this->playerId]) ? self::$_preloadData[$this->categoryId][$this->playerId] : [];
        }
    }

    protected function getCacheKey()
    {
        return 'player-question-boxes-'.$this->playerId.'-category-'.$this->categoryId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
