<?php

namespace Database\Factories\ContentCategories;

use App\Models\ContentCategories\ContentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContentCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types =  [
            ContentCategory::TYPE_COURSES,
            ContentCategory::TYPE_KEYWORDS,
            ContentCategory::TYPE_TAGS,
        ];

        return [
            'name' => $this->faker->words($this->faker->numberBetween(1, 3), true),
            'type' => $this->faker->randomElement($types),
        ];
    }
}
