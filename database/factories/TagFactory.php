<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'creator_id' => 1,
            'deleted_at' => null,
            'exclusive' => $this->faker->numberBetween(0, 1),
            'hideHighscore' => $this->faker->numberBetween(0, 1),
            'label' => $this->faker->words($this->faker->numberBetween(1, 3), true),
            'tag_group_id' => null,
        ];
    }

    public function exclusive()
    {
        return $this->state(function (array $attributes) {
            return [
                'exclusive' => true,
            ];
        });
    }

    public function notExclusive()
    {
        return $this->state(function (array $attributes) {
            return [
                'exclusive' => true,
            ];
        });
    }

    /**
     * Indicate that the tag is deleted.
     *
     * @return Factory
     */
    public function deleted()
    {
        return $this->state(function (array $attributes) {
            return [
                'deleted_at' => $this->faker->dateTime('now'),
            ];
        });
    }
}
