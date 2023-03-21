<?php
namespace App\Transformers\PublicApi;

use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Transformers\AbstractTransformer;

class CourseStatisticsTransformer extends AbstractTransformer
{

    public function transform($courseParticipation)
    {
        if (! $courseParticipation) {
            return;
        }

        $finishedAt = null;

        if($courseParticipation->finished_at) {
            $finishedAt = $courseParticipation->finished_at->toIso8601ZuluString();
        }

        $statuses = [
            null => 'started',
            '1' => 'passed',
            '0' => 'failed',
        ];

        $certificates = $courseParticipation->contentAttempts->filter(function (CourseContentAttempt $attempt) {
            if($attempt->certificate_download_url) {
                return true;
            }
            return false;
        })->transform(function (CourseContentAttempt $attempt) {
            return [
                'id' => $attempt->id,
                'course_content_id' => $attempt->course_content_id,
                'download_url' => $attempt->certificate_download_url,
            ];
        });


        $attemptsByContent = $courseParticipation->contentAttempts->groupBy('course_content_id');

        $totalDuration = $courseParticipation->course->contents->filter(function (CourseContent $content) use ($attemptsByContent, $courseParticipation) {
            $attempts = $attemptsByContent->get($content->id);
            if (!$attempts) {
                return false;
            }
            return $attempts->contains('passed', 1);
        })->sum(function ($content) {
            return $content->duration;
        });

        return [
            'id' => $courseParticipation->id,
            'user_id' => $courseParticipation->user_id,
            'course_id' => $courseParticipation->course_id,
            'started_at' => $courseParticipation->created_at->toIso8601ZuluString(),
            'status' => $statuses[$courseParticipation->passed],
            'finished_at' => $finishedAt,
            'certificates' => $certificates->values(),
            'duration' => $totalDuration,
            'updated_at' => $courseParticipation->updated_at->toIso8601ZuluString(),
        ];
    }
}
