<?php

namespace App\Rules;

use App\Models\App;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class UniqueEmail implements Rule
{
    private App $app;
    private ?User $user;
    private string $field = '';
    private $value;
    private bool $needsMail = false;

    /**
     * Create a new rule instance.
     *
     * @param App $app
     * @param User|null $user
     */
    public function __construct(App $app, User $user = null)
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
    public function passes($attribute, $value): bool
    {
        $this->field = $attribute;
        $this->value = $value;

        $appProfile = $this->app->getDefaultAppProfile();
        $profileNeedsMail = $appProfile->getValue('signup_show_email') && $appProfile->getValue('signup_show_email_mandatory') === 'mandatory';
        $this->needsMail = $profileNeedsMail || !$this->app->allowMaillessSignup();

        if (!$this->needsMail && !$value) {
            return true;
        }

        if (!$value) {
            return false;
        }

        $userQuery = User::where('email', $value)->where('app_id', $this->app->id);
        if ($this->user) {
            $userQuery->where('id', '!=', $this->user->id);
        }
        if ($userQuery->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        if ($this->needsMail) {
            return 'A different entry with "' . $this->field . '" value of "' . $this->value . '" already exists in the database. "' . $this->field . '" must be unique.';
        }
        return 'A different entry with "' . $this->field . '" value of "' . $this->value . '" already exists in the database. "' . $this->field . '" must be unique or empty.';
    }
}
