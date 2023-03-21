<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class KeysIn implements Rule
{
    private Collection $allowedKeys;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Collection $allowedKeys)
    {
        $this->allowedKeys = $allowedKeys;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */

    public function passes($attribute, $value): bool
    {
        $unknownKeys = collect(array_keys($value))->diff($this->allowedKeys);
        return $unknownKeys->isEmpty();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message():string
    {
        return ':attribute contains invalid fields';
    }
}
