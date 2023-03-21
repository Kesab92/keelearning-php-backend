<?php
namespace App\Stats\Live;

use Illuminate\Support\Collection;

interface LiveStatistic {
    public function attach(Collection $data);
}
