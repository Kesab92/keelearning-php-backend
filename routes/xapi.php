<?php

// xapi proxy
Route::any('/v2/{wbtid}/{path}', 'XapiController@proxyForceId')->where('path', '.*');
Route::any('/{path}', 'XapiController@proxy')->where('path', '.*');
