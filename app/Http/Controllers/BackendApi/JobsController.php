<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\TagRepository;
use Response;

class JobsController extends Controller
{
    /**
     * @var JobRepository
     */
    private $jobs;
    /**
     * @var TagRepository
     */
    private $tags;

    public function __construct(JobRepository $jobs, TagRepository $tags)
    {
        parent::__construct();

        $this->jobs = $jobs;
        $this->tags = $tags;
    }

    /**
     * Returns the jobs for the current app.
     *
     * @throws \Exception
     */
    public function getRunningJobs()
    {
        $tag = 'appid:'.appId();
        $jobIds = $this->tags->jobs($tag);

        $jobs = $this->jobs->getJobs($jobIds)->map(function ($job) {
            $job->payload = json_decode($job->payload);

            return $job;
        })->values();

        return Response::json([
            'jobs' => $jobs,
            'total' => $this->tags->count($tag),
        ]);
    }
}
