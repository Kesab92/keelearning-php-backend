<?php

namespace App\Models;

/**
 * App\Models\QuestionTranslation
 *
 * @property int $id
 * @property int $question_id
 * @property string $language
 * @property string $title
 * @property string|null $latex
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereLatex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionTranslation whereUpdatedAt($value)
 * @mixin IdeHelperQuestionTranslation
 */
class QuestionTranslation extends KeelearningModel
{
    use \App\Traits\Duplicatable;

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
