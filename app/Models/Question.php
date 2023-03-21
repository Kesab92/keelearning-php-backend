<?php

namespace App\Models;

use App\Models\Courses\CourseContentAttachment;

/**
 * App\Models\Question.
 *
 * @property-read \App\Models\App $app
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionAnswer[] $questionAnswers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionAttachment[] $attachments
 * @property int $id
 * @property int $app_id
 * @property string $title
 * @property bool $visible
 * @property string $category_id
 * @property int $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question ofCategoryWithId($categoryId)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question visible()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereVisible($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $answertime
 * @property int $confirmed
 * @property int|null $creator_user_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionTranslation[] $allTranslationRelations
 * @property-read int|null $all_translation_relations_count
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|CourseContentAttachment[] $courseContents
 * @property-read int|null $course_contents_count
 * @property-read \App\Models\User|null $creator_user
 * @property-read mixed $has_only_image_attachments
 * @property-read mixed $realanswertime
 * @property-read int|null $question_answers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionDifficulty[] $questionDifficulties
 * @property-read int|null $question_difficulties_count
 * @method static \Illuminate\Database\Eloquent\Builder|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question ofApp($appId)
 * @method static \Illuminate\Database\Eloquent\Builder|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereAnswertime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereConfirmed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereCreatorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question withoutIndexCards()
 * @mixin IdeHelperQuestion
 */
class Question extends KeelearningModel
{
    use \App\Traits\Duplicatable;
    use \App\Traits\Saferemovable;
    use \App\Traits\Translatable;

    protected $appends = ['realanswertime'];
    public $translated = ['title', 'latex'];

    const TYPE_SINGLE_CHOICE = 0;
    const TYPE_MULTIPLE_CHOICE = 1;
    const TYPE_BOOLEAN = 2;
    const TYPE_INDEX_CARD = 3; // Is called "Lernkarte" to distinguish from the indexcards module

    public function scopeWithoutIndexCards($query)
    {
        return $query->where('type', '!=', self::TYPE_INDEX_CARD);
    }

    public function app()
    {
        return $this->belongsTo(\App\Models\App::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function creator_user()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'creator_user_id');
    }

    public function questionDifficulties()
    {
        return $this->hasMany(\App\Models\QuestionDifficulty::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(\App\Models\QuestionAnswer::class);
    }

    public function attachments()
    {
        return $this->hasMany(\App\Models\QuestionAttachment::class);
    }

    public function courseContents()
    {
        return $this->morphMany(CourseContentAttachment::class, 'attachment');
    }

    /**
     * Scopes the returned categories to those that belong to to a category with a given id.
     *
     * @param $query
     * @param $categoryId
     * @return mixed
     */
    public function scopeOfCategoryWithId($query, $categoryId)
    {
        return $query->where('category_id', '=', $categoryId);
    }

    /**
     * The function scopes the query to visible questions.
     *
     * @param $query
     * @return mixed
     */
    public function scopeVisible($query)
    {
        return $query->where('visible', '=', 1);
    }

    /**
     * Limits the query to the scope of questions of the app with the given id.
     *
     * @param $query
     * @param $appId
     *
     * @return mixed
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', '=', $appId);
    }

    /**
     * Returns a link to the backend to edit the question.
     *
     * @return string
     */
    public function getEditLink()
    {
        return '/questions?visibility=0&edit='.$this->id;
    }

    public function getTypeIcon()
    {
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return 'boolean.png';
            case self::TYPE_SINGLE_CHOICE:
                return 'single.png';
            case self::TYPE_MULTIPLE_CHOICE:
                return 'multiple.png';
        }

        return 'single.png';
    }

    /**
     * Checks if the answer is correct.
     *
     * @param $answerIds
     *
     * @return bool
     */
    public function isCorrect($answerIds)
    {
        if (intval($answerIds) == -1) {
            return false;
        }
        switch ($this->type) {
            case self::TYPE_MULTIPLE_CHOICE:
                // Multiple Choice: In this case $questionAnswerId will be an array
                $questionAnswers = $this->questionAnswers;
                $result = 1;
                foreach ($questionAnswers as $questionAnswer) {
                    // Check if the answer is correct, but the user didn't select it
                    if ($questionAnswer->correct && ! in_array($questionAnswer->id, $answerIds)) {
                        $result = 0;
                        break;
                    }
                    // Check if the answer is wrong but the user selected it
                    if (! $questionAnswer->correct && in_array($questionAnswer->id, $answerIds)) {
                        $result = 0;
                        break;
                    }
                }
                return $result;
            case self::TYPE_INDEX_CARD:
                $answerId = $answerIds[0];
                $questionAnswer = QuestionAnswer::find($answerId);
                return $questionAnswer && $questionAnswer->correct;
            default:
                $questionAnswer = QuestionAnswer::find($answerIds);
                return $questionAnswer->correct;
        }
    }

    public function getRealanswertimeAttribute()
    {
        if ($this->answertime) {
            return $this->answertime;
        }
        $appProfile = $this->app->getDefaultAppProfile();

        return $appProfile->getValue('quiz_default_answer_time');
    }

    public function getHasOnlyImageAttachmentsAttribute()
    {
        return $this->type === self::TYPE_INDEX_CARD;
    }

    /**
     * Returns the global difficulty for this question.
     *
     * @return int|mixed
     */
    public function getDifficulty($returnNullIfUnknownDifficulty = false)
    {
        if ($difficultyEntry = $this->questionDifficulties()->whereNull('user_id')->first()) {
            return $difficultyEntry->difficulty;
        }

        if($returnNullIfUnknownDifficulty) {
            return null;
        }

        return 0;
    }

    /**
     * Returns the weighted difficulty for this user.
     *
     * @param User $user
     * @return float|int
     */
    public function getWeightedDifficulty(User $user)
    {
        $globalDifficulty = $this->getDifficulty();
        $userDifficulty = 0;
        if ($userQuestionDifficulty = $this->questionDifficulties()->where('user_id', $user->id)->first()) {
            $userDifficulty = $userQuestionDifficulty->difficulty;
        }

        return ($globalDifficulty + ($userDifficulty * 2)) / 3;
    }

    /**
     * Adds & Saves answer to question.
     *
     * @param string $content
     * @param bool $correct
     * @return float|int
     */
    public function addAnswer($content, $correct)
    {
        $answer = new QuestionAnswer();
        $answer->correct = $correct;
        $answer->content = $content;
        $this->questionAnswers()->save($answer);
    }

    public function getTypeLabel() {
        switch ($this->type) {
            case self::TYPE_SINGLE_CHOICE:
                return 'Single Choice';
            case self::TYPE_MULTIPLE_CHOICE:
                return 'Multiple Choice';
            case self::TYPE_BOOLEAN:
                return 'Ja / Nein';
            case self::TYPE_INDEX_CARD:
                return 'Lernkarte';
        }
        \Sentry::captureMessage('Invalid question type for question ' . $this->id);
        return '';
    }
}
