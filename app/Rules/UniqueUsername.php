<?php

namespace App\Rules;

use App\Models\App;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class UniqueUsername implements Rule
{
    private App $app;
    private ?User $user;
    private string $field = '';
    private $value;

    /**
     * Create a new rule instance.
     *
     * @param App $app
     * @param User|null $user
     */
    public function __construct(App $app, User $user=null)
    {
        $this->app = $app;
        $this->user = $user;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value):bool
    {
        $this->field = $attribute;
        $this->value = $value;

        if($this->user && $this->user->username === $value) {
            return true;
        }

        if ($this->app->uniqueUsernames() && User::where('username', $value)->where('app_id', $this->app->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message():string
    {
        return 'A different entry with "' . $this->field . '" value of "' . $this->value . '" already exists in the database. "' . $this->field . '" must be unique.';
    }
}
