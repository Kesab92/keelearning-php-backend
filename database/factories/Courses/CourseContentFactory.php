<?php

namespace Database\Factories\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseChapter;
use App\Models\Courses\CourseContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseContent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_chapter_id' => 1,
            'type' => CourseContent::TYPE_LEARNINGMATERIAL,
            'title' => $this->faker->word(3, true),
            'description' => $this->faker->sentences(3, true),
            'position' => $this->faker->numberBetween(0,1000),
            'visible' => true,
            'duration' => $this->faker->numberBetween(1,120),
        ];
    }


    public function certificate()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => CourseContent::TYPE_CERTIFICATE,
            ];
        });
    }
}
