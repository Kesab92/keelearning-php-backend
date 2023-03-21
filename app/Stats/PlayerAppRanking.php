<?php

namespace App\Stats;

/**
 * Calculates the ranking of the player inside the app.
 */
class PlayerAppRanking extends Statistic
{
    private $appId;
    private $playerId;

    public function __construct($appId, $playerId)
    {
        $this->appId = $appId;
        $this->playerId = $playerId;
    }

    /**
     * Returns the position of the player in the ranking of that app. returns -1, if the position could not be retrieved.
     *
     * @return int
     */
    protected function getValue()
    {
        $ranking = (new AppRanking($this->appId))->fetch();

        // Go through the ranking and search for the user
        foreach ($ranking as $position => $rankInfo) {
            // If this position has the user's id, return this position
            if ($rankInfo['id'] == $this->playerId) {
                return $position + 1;
            }
        }

        return -1;
    }

    protected function getCacheKey()
    {
        return 'app-ranking-'.$this->appId.'-'.$this->playerId;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
