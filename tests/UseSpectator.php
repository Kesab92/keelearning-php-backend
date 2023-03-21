<?php

namespace Tests;

use Spectator\Spectator;

trait UseSpectator
{
    public function setUp(): void
    {
        parent::setUp();
        Spectator::using('1.0.0');
    }
}
