<?php

namespace Database\Factories;

use App\Models\App;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'app_id' => App::ID_KEEUNIT_DEMO,
            'visible' => $this->faker->boolean(),
            'public' => $this->faker->boolean(),
            'show_on_auth' => $this->faker->boolean(),
            'show_in_footer' => $this->faker->boolean(),
            'title' => $this->faker->words($this->faker->numberBetween(1, 3), true),
            'content' => collect($this->faker->paragraphs($this->faker->numberBetween(1, 10)))
                ->map(function ($paragraph) { return '<p>' . $paragraph . '</p>'; })
                ->join(''),
        ];
    }
}
