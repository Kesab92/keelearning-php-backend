<?php

namespace App\Console\Commands;

use App\Models\Courses\Course;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateRepeatedCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:repeatedcourses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates repeated courses.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today();

        $courses = Course
            ::template()
            ->where('is_repeating', 1)
            ->where('visible', 1)
            ->with(
                'latestRepeatedCourse',
                'allTranslationRelations',
                'tags',
                'managers',
                'previewTags',
                'chapters.contents.tags',
                'chapters.allTranslationRelations',
                'categories.allTranslationRelations',
                'awardTags',
                'retractTags',
            )
            ->get();

        $repeatedCourses = DB
            ::table('courses')
            ->select(DB::raw('parent_course_id, MAX(created_at) as created_at, COUNT(parent_course_id) as count'))
            ->whereIn('parent_course_id', $courses->pluck('id'))
            ->groupBy('parent_course_id')
            ->get()
            ->keyBy('parent_course_id');

        $coursesToRepeat = $courses->filter(function ($course) use ($today, $repeatedCourses) {
            $nextRepetitionDate = $course->getNextRepetitionDate(true);

            if (!$nextRepetitionDate) {
                return false;
            }

            if($repeatedCourses->has($course->id)) {
                if($course->repetition_count !== null && $repeatedCourses->get($course->id)->count >= $course->repetition_count) {
                    return false;
                }
            }

            return $nextRepetitionDate->lessThanOrEqualTo($today);
        });

        foreach($coursesToRepeat as $course) {
            $nextRepetitionDate = $course->getNextRepetitionDate(true);

            if($course->time_limit) {
                $availableUntil = $nextRepetitionDate->clone();
                switch ($course->time_limit_type) {
                    case Course::INTERVAL_WEEKLY:
                        $availableUntil->addWeeks($course->time_limit);
                        break;
                    case Course::INTERVAL_MONTHLY:
                        $availableUntil->addMonths($course->time_limit);
                        break;
                }
                $availableUntil->setTime(23, 59, 59);
            } else {
                $availableUntil = null;
            }

            $availableFrom = $nextRepetitionDate;

            $newCourse = $course->duplicate();
            $newCourse->creator_id = $course->creator_id;
            $newCourse->parent_course_id = $course->id;
            $newCourse->available_from = $availableFrom;
            $newCourse->available_until = $availableUntil;
            $newCourse->visible = true;
            $newCourse->is_repeating = false;
            $newCourse->is_template = false;
            foreach($newCourse->allTranslationRelations()->get() as $translation) { // FIXME: Saving of translation changes doesn't work if I use $newCourse->allTranslationRelations
                $translation->title =  $translation->title . ' ' . $today->weekOfYear . '/'.$today->year;
                $translation->save();
            }
            $newCourse->save();
        }

        return 0;
    }
}
