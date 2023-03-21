<?php

namespace Tests;

use App\Services\Sorting;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class SortingTest extends TestCase
{
    public function testSortRank()
    {
        $sortedArray = [
            [
                'id' => 1,
                'win_count' => 10,
                'correct_answers_count' => 100,
            ], [
                'id' => 2,
                'win_count' => 9,
                'correct_answers_count' => 90,
            ], [
                'id' => 3,
                'win_count' => 8,
                'correct_answers_count' => 85,
            ], [
                'id' => 4,
                'win_count' => 8,
                'correct_answers_count' => 80,
            ], [
                'id' => 5,
                'win_count' => 0,
                'correct_answers_count' => 0,
            ],
        ];

        $arrayToSort = [
            [
                'id' => 5,
                'win_count' => 0,
                'correct_answers_count' => 0,
            ], [
                'id' => 2,
                'win_count' => 9,
                'correct_answers_count' => 90,
            ], [
                'id' => 4,
                'win_count' => 8,
                'correct_answers_count' => 80,
            ], [
                'id' => 3,
                'win_count' => 8,
                'correct_answers_count' => 85,
            ], [
                'id' => 1,
                'win_count' => 10,
                'correct_answers_count' => 100,
            ],
        ];

        $tmp = Sorting::sortRankByGameWinsAndCorrectAnswers($arrayToSort);
        $this->assertTrue($tmp == $sortedArray);
    }
}
