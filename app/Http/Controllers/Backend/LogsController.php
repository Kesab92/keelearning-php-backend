<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;

class LogsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.superadmin');
    }

    /**
     * Displays daily logs.
     *
     * @return mixed
     * @throws \Exception
     */
    public function overview($day = null)
    {
        $avgresponse = [];
        $requestcount = [];
        for ($i = 0; $i <= 24; $i++) {
            $avgresponse[$i] = 0;
            $requestcount[$i] = 0;
        }

        $data = [];

        if ($day == null) {
            $filepath = storage_path('logs/access-log-'.date('Y-m-d').'.log');
        } else {
            $filepath = storage_path('logs/access-log-'.$day.'.log');
        }
        $handle = fopen($filepath, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $data[] = $line;
            }

            fclose($handle);
        } else {
            throw new \Exception('Cant open file');
        }

        $hourly = [];
        $byurl = [];
        $reqs = [];
        foreach ($data as $line) {
            $expl = explode(' ', $line);
            if ($expl[3] == 'OPTIONS') {
                continue;
            }
            $time = substr($expl[1], 0, 8);
            $ts = strtotime('2016-06-01 '.$time);
            $slot = date('H:i:', $ts).floor(date('s', $ts) / 6);
            if (! isset($reqs[$slot])) {
                $reqs[$slot] = 0;
            }
            $reqs[$slot]++;
            $hour = substr($expl[1], 0, 2);
            if (! isset($hourly[$hour])) {
                $hourly[$hour] = [];
            }
            $hourly[$hour][] = $expl[7];

            $url = $expl[3].' '.$expl[4];
            if (! isset($byurl[$url])) {
                $byurl[$url] = [];
            }
            $byurl[$url][] = $expl[7];
        }
        arsort($reqs);
        $peak = [
            'time' => array_keys($reqs)[0],
            'requests' => reset($reqs),
        ];
        foreach ($hourly as $hour => $times) {
            $avgresponse[intval($hour)] = array_sum($times) / count($times);
        }

        foreach ($hourly as $hour => $times) {
            $requestcount[intval($hour)] = count($times);
        }

        foreach ($byurl as $url => $times) {
            $byurl[$url] = array_sum($times) / count($times).' Max: '.max($times).' Count: '.count($times);
        }

        arsort($byurl);

        return view('logs.overview', [
            'avgresponse' => $avgresponse,
            'requestcount' => $requestcount,
            'byurl' => $byurl,
            'peak' => $peak,
        ]);
    }
}
