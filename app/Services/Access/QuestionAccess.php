<?php

namespace App\Services\Access;

use App\Models\CategoryHider;
use App\Models\Question;
use App\Models\User;
use Exception;

class QuestionAccess implements AccessInterface
{
    /**
     * Attention: This does not check for visibility, as admins might be allowed to see invisible questions.
     *
     * @param User $user
     * @param Question $resource
     * @return bool
     * @throws Exception
     */
    public function hasAccess(User $user, $resource)
    {
        $quizCategories = $user->getQuestionCategories(CategoryHider::SCOPE_QUIZ, true);
        $powerlearningCategories = $user->getQuestionCategories(CategoryHider::SCOPE_TRAINING, true);

        $categories = $quizCategories->merge($powerlearningCategories);

        return $categories->contains('id', '=', $resource->category_id);
    }
}
