<?php

namespace Database\Factories\Courses;

use App\Models\Courses\Course;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseParticipation;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseContentAttemptFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseContentAttempt::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $passed = $this->faker->randomElement([
            true,
            false,
            null,
        ]);
        $createdAt = $this->faker->dateTime;
        return [
            'course_content_id' => 1,
            'course_participation_id' => 1,
            'passed' => $passed,
            'created_at' => $createdAt,
            'finished_at' => is_null($passed) ? null : $this->faker->dateTimeBetween($createdAt, 'now'),
        ];
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'passed' => false,
                'finished_at' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
            ];
        });
    }

    public function passed()
    {
        return $this->state(function (array $attributes) {
            return [
                'passed' => true,
                'finished_at' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
            ];
        });
    }
}
