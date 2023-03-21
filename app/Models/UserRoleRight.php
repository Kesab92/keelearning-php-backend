<?php

namespace App\Models;

use App\Traits\Duplicatable;

/**
 * @mixin IdeHelperUserRoleRight
 */
class UserRoleRight extends KeelearningModel
{
    use Duplicatable;

    const RIGHT_TYPES = [
        'advertisements-edit',
        'appointments-edit',
        'appointments-view',
        'categories-edit',
        'comments-personaldata',
        'competitions-edit',
        'competitions-personaldata',
        'competitions-showemails',
        'courses-edit',
        'courses-personaldata',
        'courses-showemails',
        'courses-stats',
        'courses-view',
        'dashboard-personaldata',
        'dashboard-userdata',
        'forms-edit',
        'forms-stats',
        'indexcards-edit',
        'keywords-edit',
        'learningmaterials-edit',
        'learningmaterials-personaldata',
        'learningmaterials-stats',
        'mails-edit',
        'news-edit',
        'pages-edit',
        'questions-edit',
        'questions-stats',
        'quizteams-personaldata',
        'quizteams-showemails',
        'settings-edit',
        'settings-ratings',
        'settings-viewcounts',
        'suggestedquestions-edit',
        'suggestedquestions-personaldata',
        'tags-edit',
        'tests-edit',
        'tests-personaldata',
        'tests-showemails',
        'tests-stats',
        'tests-view',
        'users-edit',
        'users-export',
        'users-personaldata',
        'users-showemails',
        'users-stats',
        'users-view',
        'vouchers-edit',
        'vouchers-personaldata',
        'vouchers-showemails',
        'webinars-personaldata',
        'webinars-showemails',
    ];

    public $fillable = ['right'];

    public function userRole()
    {
        return $this->belongsTo(UserRole::class);
    }
}
