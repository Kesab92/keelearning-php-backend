<?php

namespace Database\Factories;
use App\Models\LearningMaterialFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

class LearningMaterialFolderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LearningMaterialFolder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'app_id' => 1,
            'name' => $this->faker->words(3, true),
        ];
    }
}
