<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class DevController extends Controller
{
    public function resetOpcache()
    {
        opcache_reset();
    }
}
