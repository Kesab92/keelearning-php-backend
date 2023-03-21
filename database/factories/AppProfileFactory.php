<?php

namespace Database\Factories;

use App\Models\AppProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_default' => false,
            'name' => $this->faker->company,
        ];
    }

    public function default()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_default' => true,
                'name' => 'Standard-Profil',
            ];
        });
    }
}
