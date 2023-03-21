<?php

namespace App\Models;

use Exception;

/**
 * App\Models\UserPermission
 *
 * @property int $id
 * @property int $user_id
 * @property string $permission
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPermission whereUserId($value)
 * @mixin IdeHelperUserPermission
 */
class UserPermission extends KeelearningModel
{
    const PERMISSION_TYPES = [
        'advertisements',
        'categories',
        'comments',
        'competitions',
        'courses-readonly',
        'courses-stats',
        'courses',
        'groups',
        'import',
        'index_cards',
        'keywords',
        'learningmaterials',
        'mails',
        'manage-user_rights',
        'news',
        'pages',
        'questions-suggested',
        'questions',
        'settings',
        'stats',
        'tags',
        'tests-stats',
        'tests',
        'user-export',
        'users-readonly',
        'users',
        'views',
        'vouchers',
        'webinars',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public static function hadPermission(User $user, string $permission): bool
    {
        if (!in_array($permission, self::PERMISSION_TYPES)) {
            throw new Exception('Permission does not exist:' . $permission);
        }
        return $user->permissions->pluck('permission')->contains($permission);
    }

    public static function hasEquivalentRight(User $user, string $right): bool
    {
        // the permissions `import` and `manage-user_rights` have no 1:1 equivalent
        // - `import` allowed access to all imports, now separated by the edit-rights
        // - `manage-user_rights` is now main admin only
        switch ($right) {
            case 'advertisements-edit':
                return self::hadPermission($user, 'advertisements');
            case 'categories-edit':
                return self::hadPermission($user, 'categories');
            case 'comments-personaldata':
                return self::hadPermission($user, 'comments');
            case 'competitions-edit':
            case 'competitions-personaldata':
            case 'competitions-showemails':
                return self::hadPermission($user, 'competitions');
            case 'courses-edit':
                return self::hadPermission($user, 'courses');
            case 'courses-personaldata':
            case 'courses-showemails':
            case 'courses-stats':
                return self::hadPermission($user, 'courses-stats');
            case 'courses-view':
                return !self::hadPermission($user, 'courses') && self::hadPermission($user, 'courses-readonly');
            case 'quizteams-personaldata':
            case 'quizteams-showemails':
                return self::hadPermission($user, 'groups');
            case 'indexcards-edit':
                return self::hadPermission($user, 'index_cards');
            case 'keywords-edit':
                return self::hadPermission($user, 'keywords');
            case 'learningmaterials-edit':
                return self::hadPermission($user, 'learningmaterials');
            case 'learningmaterials-personaldata':
            case 'learningmaterials-stats':
                return self::hadPermission($user, 'stats');
            case 'mails-edit':
                return self::hadPermission($user, 'mails');
            case 'news-edit':
                return self::hadPermission($user, 'news');
            case 'pages-edit':
                return self::hadPermission($user, 'pages');
            case 'questions-edit':
                return self::hadPermission($user, 'questions');
            case 'questions-stats':
                return self::hadPermission($user, 'stats');
            case 'settings-edit':
                return self::hadPermission($user, 'settings');
            case 'settings-ratings':
                return self::hadPermission($user, 'stats');
            case 'settings-viewcounts':
                return self::hadPermission($user, 'views');
            case 'suggestedquestions-edit':
            case 'suggestedquestions-personaldata':
                return self::hadPermission($user, 'questions-suggested');
            case 'tags-edit':
                return self::hadPermission($user, 'tags');
            case 'tests-edit':
                return self::hadPermission($user, 'tests');
            case 'tests-personaldata':
            case 'tests-showemails':
            case 'tests-stats':
                return self::hadPermission($user, 'tests-stats');
            case 'tests-view':
                return false; // had no readonly permission before
            case 'users-edit':
                return self::hadPermission($user, 'users');
            case 'users-export':
                return self::hadPermission($user, 'users') && self::hadPermission($user, 'user-export');
            case 'users-personaldata':
            case 'users-showemails':
                return self::hadPermission($user, 'stats') || self::hadPermission($user, 'users') || self::hadPermission($user, 'users-readonly');
            case 'users-stats':
                return self::hadPermission($user, 'stats');
            case 'users-view':
                return !self::hadPermission($user, 'users') && self::hadPermission($user, 'users-readonly');
            case 'vouchers-edit':
            case 'vouchers-personaldata':
            case 'vouchers-showemails':
                return self::hadPermission($user, 'vouchers');
            case 'webinars-personaldata':
            case 'webinars-showemails':
                return self::hadPermission($user, 'webinars');
            default:
                return false;
        }
    }
}
