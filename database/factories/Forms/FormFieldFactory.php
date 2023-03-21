<?php

namespace Database\Factories\Forms;

use App\Models\Forms\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFieldFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FormField::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'form_id' => 1,
            'is_required' => 0,
            'position' => 1,
            'title' => $this->faker->words(3, true),
            'type' => FormField::TYPE_TEXTAREA,
        ];
    }
}
