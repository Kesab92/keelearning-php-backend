<?php

namespace App\Models;

/**
 * App\Models\WebinarAdditionalUser
 *
 * @property int $id
 * @property int $webinar_id
 * @property int $user_id
 * @property string $email
 * @property string $name
 * @property int $role
 * @property-read User $user
 * @property-read Webinar $webinar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\WebinarParticipant|null $participant
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarAdditionalUser whereWebinarId($value)
 * @mixin IdeHelperWebinarAdditionalUser
 */
class WebinarAdditionalUser extends KeelearningModel
{
    const ROLE_MODERATOR = 1;
    const ROLE_PARTICIPANT = 2;
    const ROLE_OBSERVER = 3;

    public function webinar()
    {
        return $this->belongsTo(Webinar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participant()
    {
        return $this->hasOne(WebinarParticipant::class);
    }
}
