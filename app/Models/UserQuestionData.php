<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperUserQuestionData
 */
class UserQuestionData extends KeelearningModel
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function scopeOfUser($query, User $user)
    {
        return $query->where('user_id', '=', $user->id);
    }

    public function scopeOfQuestion($query, Question $question)
    {
        return $query->where('question_id', '=', $question->id);
    }
}
