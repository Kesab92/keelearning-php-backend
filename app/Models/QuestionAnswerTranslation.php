<?php

namespace App\Models;

/**
 * App\Models\QuestionAnswerTranslation
 *
 * @property int $id
 * @property int $question_answer_id
 * @property string $language
 * @property string $content
 * @property string|null $feedback
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\QuestionAnswer $questionAnswer
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereQuestionAnswerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionAnswerTranslation whereUpdatedAt($value)
 * @mixin IdeHelperQuestionAnswerTranslation
 */
class QuestionAnswerTranslation extends KeelearningModel
{
    use \App\Traits\Duplicatable;

    public function questionAnswer()
    {
        return $this->belongsTo(QuestionAnswer::class);
    }
}
