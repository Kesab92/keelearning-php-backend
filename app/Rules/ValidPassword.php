<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidPassword implements Rule
{
    private array $badwords;

    /**
     * Create a new rule instance.
     *
     * @param array $badwords
     */
    public function __construct(array $badwords = [])
    {
        $this->badwords = $badwords;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $passwordValidationResult = validatePassword($value, $this->badwords);
        if ($passwordValidationResult['valid'] === false) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The password is invalid.';
    }
}
