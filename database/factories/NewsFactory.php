<?php

namespace Database\Factories;

use App\Models\App;
use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = News::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'app_id' => App::ID_KEEUNIT_DEMO,
            'send_notification' => false,
            'title' => $this->faker->words($this->faker->numberBetween(1, 3), true),
            'content' => collect($this->faker->paragraphs($this->faker->numberBetween(1, 10)))
                ->map(function ($paragraph) { return '<p>' . $paragraph . '</p>'; })
                ->join(''),
        ];
    }

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => $this->faker->dateTimeBetween('-5 years', 'now'),
            ];
        });
    }
}
