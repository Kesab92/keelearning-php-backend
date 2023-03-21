<?php
namespace App\Transformers\PublicApi;

use App\Transformers\AbstractTransformer;
use App\Models\Courses\Course;

class CourseTransformer extends AbstractTransformer
{

    /**
     * @param Course $course
     * @return array|void
     */
    public function transform($course)
    {
        if (! $course) {
            return;
        }

        $startsAt = null;
        $endsAt = null;
        if($course->available_from) {
            $startsAt = $course->available_from->toIso8601ZuluString();
        }
        if ($course->duration_type == Course::DURATION_TYPE_FIXED) {
            if($course->available_until) {
                $endsAt = $course->available_until->toIso8601ZuluString();
            }
        }

        $participationDuration = null;
        $participationDurationType = null;
        if($course->duration_type === Course::DURATION_TYPE_DYNAMIC) {
            $participationDuration = $course->participation_duration;
            $durationTypeMap = [
                Course::PARTICIPATION_DURATION_DAYS => 'days',
                Course::PARTICIPATION_DURATION_WEEKS => 'weeks',
                Course::PARTICIPATION_DURATION_MONTHS => 'months',
            ];
            $participationDurationType = $durationTypeMap[$course->participation_duration_type];
        }

        return [
            'id' => $course->id,
            'title' => $course->title,
            'is_visible' => !!$course->visible,
            'is_mandatory' => !!$course->is_mandatory,
            'created_at' => $course->created_at->toIso8601ZuluString(),
            'starts_at' => $startsAt,
            'duration_type' => $course->duration_type === Course::DURATION_TYPE_FIXED ? 'fixed' : 'dynamic',
            'ends_at' => $endsAt,
            'participation_duration' => $participationDuration,
            'participation_duration_type' => $participationDurationType,
            'max_duration' => $course->getCourseMaxDuration(),
            'category_ids' => $course->categories()->allRelatedIds(),
        ];
    }
}
