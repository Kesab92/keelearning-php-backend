<?php

namespace App\Rules;

use App\Models\Category;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class CategoryTagRights implements Rule
{
    private User $user;
    private string $field = '';
    private $value;
    private $currentValue;

    /**
     * Create a new rule instance.
     *
     * @param User|null $user
     */
    public function __construct(User $user, $currentValue)
    {
        $this->user = $user;
        $this->currentValue = $currentValue;
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

        if($this->currentValue == $this->value) {
            return true;
        }

        if($this->user->isFullAdmin()) {
            return true;
        }

        $category = Category::find($this->value);

        if(!$category) {
            return false;
        }

        if($category->tags->isEmpty()) {
            return true;
        }

        $userTagRightIds = $this->user->tagRightsRelation->pluck('id');
        $tagIds = $category->tags->pluck('id');

        return $tagIds->intersect($userTagRightIds)->isNotEmpty();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'You do not have access to the category with ID "'.$this->value.'".';
    }
}
