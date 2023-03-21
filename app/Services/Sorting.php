<?php

namespace App\Services;

class Sorting
{
    /**
     * Sort an array that contains app ranking data. it must be in the following format
     * [['id', 'win_count', 'correct_answers_count'], ...].
     *
     * @param array $arrayToSort
     * @return array
     */
    public static function sortRankByGameWinsAndCorrectAnswers(array $arrayToSort)
    {
        usort($arrayToSort, function ($a, $b) {

            // If the wincount is different
            if ($a['win_count'] < $b['win_count']) {
                return 1;
            }

            // If the wincount is equal, sort by the number of correct answers given
            if ($a['win_count'] == $b['win_count']) {
                if ($a['correct_answers_count'] < $b['correct_answers_count']) {
                    return 1;
                }
                if ($a['correct_answers_count'] == $b['correct_answers_count']) {
                    return 0;
                }

                return -1;
            }

            return -1;
        });

        return $arrayToSort;
    }
}
