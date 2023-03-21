<?php

namespace App\Models;

/**
 * App\Models\SuggestedQuestion.
 *
 * @property-read \App\Models\App $app
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SuggestedQuestionAnswer[] $questionAnswers
 * @property int $id
 * @property int $app_id
 * @property int $category_id
 * @property string $title
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\SuggestedQuestion whereCategoryId($value)
 * @mixin \Eloquent
 * @property-read int|null $question_answers_count
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SuggestedQuestion query()
 * @mixin IdeHelperSuggestedQuestion
 */
class SuggestedQuestion extends KeelearningModel
{
    use \App\Traits\Saferemovable;

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(\App\Models\SuggestedQuestionAnswer::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }
}
