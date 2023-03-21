<?php

namespace Database\Factories\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseChapterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseChapter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => 1,
            'title' => $this->faker->words(3, true),
            'position' => $this->faker->numberBetween(0,1000),
        ];
    }
}
