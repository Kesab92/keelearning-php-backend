<?php

namespace App\Models;

use App\Services\AccessLogMeta\AccessLogAppProfileChanged;
use App\Services\AccessLogMeta\AccessLogUserAdd;
use App\Services\AccessLogMeta\AccessLogUserDelete;
use App\Services\AccessLogMeta\AccessLogUserPasswordReset;
use App\Services\AccessLogMeta\AccessLogUserSignup;
use App\Services\AccessLogMeta\AccessLogUserUpdate;
use App\Services\AccessLogMeta\Appointments\AccessLogAppointmentCreate;
use App\Services\AccessLogMeta\Appointments\AccessLogAppointmentDelete;
use App\Services\AccessLogMeta\Courses\AccessLogCourseChapterDelete;
use App\Services\AccessLogMeta\Courses\AccessLogCourseDelete;
use App\Services\AccessLogMeta\Forms\AccessLogFormCreate;
use App\Services\AccessLogMeta\Forms\AccessLogFormDelete;
use App\Services\AccessLogMeta\Forms\AccessLogFormUpdate;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionAddAttachment;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionCreate;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionDelete;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionRemoveAttachment;
use App\Services\AccessLogMeta\Questions\AccessLogQuestionUpdate;
use App\Services\AccessLogMeta\UserRoles\AccessLogUserRoleCreate;
use App\Services\AccessLogMeta\UserRoles\AccessLogUserRoleDelete;
use App\Services\AccessLogMeta\UserRoles\AccessLogUserRoleUpdate;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin IdeHelperAccessLog
 */
class AccessLog extends KeelearningModel
{
    const ACTION_LOGIN = 0;
    // Users
    const ACTION_USER_SIGNUP = 1;
    const ACTION_USER_UPDATE = 2;
    const ACTION_USER_DELETE = 3;
    const ACTION_USER_PASSWORD_RESET = 4;
    const ACTION_USER_ADD = 13;
    // Questions
    const ACTION_QUESTION_DELETE = 5;
    const ACTION_QUESTION_CREATE = 6;
    const ACTION_QUESTION_UPDATE = 7;
    const ACTION_QUESTION_ADD_ATTACHMENT = 8;
    const ACTION_QUESTION_REMOVE_ATTACHMENT = 9;
    const ACTION_UPDATE_APP_PROFILE_SETTING = 10;
    // Courses
    const ACTION_DELETE_COURSE = 11;
    const ACTION_DELETE_COURSE_CHAPTER = 12;
    // Appointments
    const ACTION_DELETE_APPOINTMENT = 14;
    const ACTION_APPOINTMENT_CREATE = 15;
    // Forms
    const ACTION_DELETE_FORM = 16;
    const ACTION_FORM_CREATE = 17;
    const ACTION_FORM_UPDATE = 18;
    // User Roles
    const ACTION_DELETE_USER_ROLE = 19;
    const ACTION_USER_ROLE_CREATE = 20;
    const ACTION_USER_ROLE_UPDATE = 21;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabel()
    {
        switch ($this->action) {
            case self::ACTION_LOGIN:
                return 'Login';
            case self::ACTION_USER_SIGNUP:
                return 'Benutzer erstellt';
            case self::ACTION_USER_ADD:
                return 'Benutzer eingeladen';
            case self::ACTION_USER_UPDATE:
                return 'Benutzer bearbeitet';
            case self::ACTION_USER_DELETE:
                return 'Benutzer gelöscht';
            case self::ACTION_USER_PASSWORD_RESET:
                return 'Passwort zurückgesetzt';
            case self::ACTION_QUESTION_DELETE:
                return 'Frage gelöscht';
            case self::ACTION_QUESTION_CREATE:
                return 'Frage erstellt';
            case self::ACTION_QUESTION_UPDATE:
                return 'Frage bearbeitet';
            case self::ACTION_QUESTION_ADD_ATTACHMENT:
                return 'Anhang einer Frage hinzugefügt';
            case self::ACTION_QUESTION_REMOVE_ATTACHMENT:
                return 'Anhang einer Frage gelöscht';
            case self::ACTION_UPDATE_APP_PROFILE_SETTING:
                return 'App Profil Einstellung geändert';
            case self::ACTION_DELETE_COURSE:
                return 'Kurs gelöscht';
            case self::ACTION_DELETE_COURSE_CHAPTER:
                return 'Kurs Kapitel gelöscht';
            case self::ACTION_DELETE_APPOINTMENT:
                return 'Termin gelöscht';
            case self::ACTION_APPOINTMENT_CREATE:
                return 'Termin erstellt';
            case self::ACTION_DELETE_FORM:
                return 'Formular gelöscht';
            case self::ACTION_FORM_CREATE:
                return 'Formular erstellt';
            case self::ACTION_FORM_UPDATE:
                return 'Formular bearbeitet';
            case self::ACTION_DELETE_USER_ROLE:
                return 'User Rolle gelöscht';
            case self::ACTION_USER_ROLE_CREATE:
                return 'User Rolle erstellt';
            case self::ACTION_USER_ROLE_UPDATE:
                return 'User Rolle bearbeitet';
            default:
                return 'Unbekannte Aktion';
        }
    }

    public function getMeta()
    {
        switch ($this->action) {
            case self::ACTION_USER_UPDATE:
                return AccessLogUserUpdate::displayMeta($this->meta);
            case self::ACTION_USER_PASSWORD_RESET:
                return AccessLogUserPasswordReset::displayMeta($this->meta);
            case self::ACTION_USER_DELETE:
                return AccessLogUserDelete::displayMeta($this->meta);
            case self::ACTION_USER_SIGNUP:
                return AccessLogUserSignup::displayMeta($this->meta);
            case self::ACTION_USER_ADD:
                return AccessLogUserAdd::displayMeta($this->meta);
            case self::ACTION_QUESTION_DELETE:
                return AccessLogQuestionDelete::displayMeta($this->meta);
            case self::ACTION_QUESTION_CREATE:
                return AccessLogQuestionCreate::displayMeta($this->meta);
            case self::ACTION_QUESTION_UPDATE:
                return AccessLogQuestionUpdate::displayMeta($this->meta);
            case self::ACTION_QUESTION_ADD_ATTACHMENT:
                return AccessLogQuestionAddAttachment::displayMeta($this->meta);
            case self::ACTION_QUESTION_REMOVE_ATTACHMENT:
                return AccessLogQuestionRemoveAttachment::displayMeta($this->meta);
            case self::ACTION_UPDATE_APP_PROFILE_SETTING:
                return AccessLogAppProfileChanged::displayMeta($this->meta);
            case self::ACTION_DELETE_COURSE:
                return AccessLogCourseDelete::displayMeta($this->meta);
            case self::ACTION_DELETE_COURSE_CHAPTER:
                return AccessLogCourseChapterDelete::displayMeta($this->meta);
            case self::ACTION_DELETE_APPOINTMENT:
                return AccessLogAppointmentDelete::displayMeta($this->meta);
            case self::ACTION_APPOINTMENT_CREATE:
                return AccessLogAppointmentCreate::displayMeta($this->meta);
            case self::ACTION_DELETE_FORM:
                return AccessLogFormDelete::displayMeta($this->meta);
            case self::ACTION_FORM_CREATE:
                return AccessLogFormCreate::displayMeta($this->meta);
            case self::ACTION_FORM_UPDATE:
                return AccessLogFormUpdate::displayMeta($this->meta);
            case self::ACTION_DELETE_USER_ROLE:
                return AccessLogUserRoleDelete::displayMeta($this->meta);
            case self::ACTION_USER_ROLE_CREATE:
                return AccessLogUserRoleCreate::displayMeta($this->meta);
            case self::ACTION_USER_ROLE_UPDATE:
                return AccessLogUserRoleUpdate::displayMeta($this->meta);
            default:
                return '';
        }
    }
}
