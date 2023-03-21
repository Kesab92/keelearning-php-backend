<?php

namespace Database\Factories\Courses;

use App\Models\Courses\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'app_id' => 1,
            'archived_at' => null,
            'creator_id' => null,
            'description' => $this->faker->sentences(2, true),
            'is_mandatory' => $this->faker->boolean(),
            'is_repeating' => false,
            'is_template' => false,
            'parent_course_id' => null,
            'preview_enabled' => $this->faker->boolean(),
            'repetition_count' => null,
            'repetition_interval_type' => null,
            'repetition_interval' => null,
            'send_new_course_notification' => $this->faker->boolean(),
            'send_passed_course_mail' => $this->faker->boolean(),
            'send_repetition_course_reminder' => false,
            'time_limit_type' => 0,
            'time_limit' => 0,
            'title' => $this->faker->words(3, true),
            'visible' => $this->faker->boolean(),
        ];
    }

    public function template()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_template' => true,
            ];
        });
    }
}
