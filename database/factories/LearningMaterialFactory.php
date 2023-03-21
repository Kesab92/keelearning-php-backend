<?php

namespace Database\Factories;
use App\Models\LearningMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

class LearningMaterialFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LearningMaterial::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'learning_material_folder_id' => 1,
            'visible' => 1,
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentences(2, true),
            'download_disabled' => 0,
            'show_watermark' => 0,
        ];
    }
}
