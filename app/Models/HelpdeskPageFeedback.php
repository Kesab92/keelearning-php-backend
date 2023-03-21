<?php

namespace App\Models;

/**
 * App\Models\HelpdeskPageFeedback
 *
 * @property int $id
 * @property int $page_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpdeskPageFeedback whereUserId($value)
 * @mixin IdeHelperHelpdeskPageFeedback
 */
class HelpdeskPageFeedback extends KeelearningModel
{
    protected $table = 'helpdesk_page_feedbacks';
}
