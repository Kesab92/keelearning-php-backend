<?php

namespace Database\Factories;

use App\Models\User;
use Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'active' => $this->faker->boolean(),
            'app_id' => 1,
            'country' => $this->faker->countryCode(),
            'email' => $this->faker->safeEmail(),
            'firstname' => $this->faker->firstName(),
            'is_admin' => false,
            'is_api_user' => false,
            'is_bot' => false,
            'is_dummy' => false,
            'is_keeunit' => false,
            'user_role_id' => null,
            'language' => $this->faker->randomElement([
                'de',
                'en',
            ]),
            'lastname' => $this->faker->lastName(),
            'password' => Hash::make($this->faker->password()),
            'tos_accepted' => false,
            'username' => $this->faker->userName(),
        ];
    }

    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'active' => true,
                'deleted_at' => null,
                'tos_accepted' => true,
            ];
        });
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_admin' => true,
            ];
        });
    }

    public function dummy()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_dummy' => true,
            ];
        });
    }
}
