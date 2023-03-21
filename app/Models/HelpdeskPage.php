<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

/**
 * App\Models\HelpdeskPage
 *
 * @property int $id
 * @property string $title
 * @property string $type
 * @property string $content
 * @property int|null $category
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\HelpdeskPageFeedback[] $feedbacks
 * @property-read int|null $feedbacks_count
 * @property-read mixed $feedback_count
 * @property-read mixed $has_authenticated_user_feedback
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPage whereUpdatedAt($value)
 * @mixin IdeHelperHelpdeskPage
 */
class HelpdeskPage extends KeelearningModel
{
    const CATEGORY_FAQ = 'faq';
    const CATEGORY_KNOWLEDGE_BASE = 'knowledge';

    protected $appends = [
        'hasAuthenticatedUserFeedback',
        'feedbackCount',
    ];

    /**
     * Checks if this page has a feedback by authenticated user.
     */
    public function getHasAuthenticatedUserFeedbackAttribute()
    {
        return HelpdeskPageFeedback::where('page_id', $this->attributes['id'])
                ->where('user_id', Auth::user()->id)
                ->count() > 0;
    }

    /**
     * Returns the count of feedback by all users.
     */
    public function getFeedbackCountAttribute()
    {
        return $this->feedbacks()->count();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function feedbacks()
    {
        return $this->hasMany(HelpdeskPageFeedback::class, 'page_id');
    }
}
