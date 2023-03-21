<?php

namespace Tests;

use App\Models\Competition;
use App\Services\Referee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class RefereeTest extends TestCase
{
    public function testWithinBuffer()
    {
        $competition = new Competition();
        $competition->created_at = Carbon::now();
        $competition->duration = 1;
        $competition->save();

        $this->assertFalse(Referee::withinTimeBuffer($competition->id));

        $competition->created_at = Carbon::now()->subDays(2);
        $competition->save();

        $this->assertFalse(Referee::withinTimeBuffer($competition->id, 0));
        $this->assertFalse(Referee::withinTimeBuffer($competition->id, 2));

        $competition->created_at = Carbon::now()->subDays(1)->subMinutes(2);
        $competition->save();

        $this->assertTrue(Referee::withinTimeBuffer($competition->id, 0, 5));
    }
}
