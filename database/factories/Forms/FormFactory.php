<?php

namespace Database\Factories\Forms;

use App\Models\Forms\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'app_id' => 1,
            'created_by_id' => 1,
            'is_archived' => 0,
            'is_draft' => 0,
            'last_updated_by_id' => 1,
            'title' => $this->faker->words(3, true),
        ];
    }
}
