<?php

namespace App\Models;

/**
 * App\Models\WebinarParticipant
 *
 * @property int $webinar_id
 * @property int $user_id
 * @property int $webinar_additional_user_id
 * @property string $join_link
 * @property-read User $user
 * @property-read Webinar $webinar
 * @property-read WebinarAdditionalUser $additionalUser
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $samba_invitee_id
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereJoinLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereSambaInviteeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereWebinarAdditionalUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebinarParticipant whereWebinarId($value)
 * @mixin IdeHelperWebinarParticipant
 */
class WebinarParticipant extends KeelearningModel
{
    public function webinar()
    {
        return $this->belongsTo(Webinar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function additionalUser()
    {
        return $this->belongsTo(WebinarAdditionalUser::class);
    }
}
