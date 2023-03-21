<?php

namespace App\Models;

/**
 * App\Models\LearnBoxCard
 *
 * @property int $id
 * @property int $user_id
 * @property int $foreign_id
 * @property int $type
 * @property int $box
 * @property array $userdata
 * @property string $box_entered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereBox($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereBoxEnteredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereForeignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LearnBoxCard whereUserdata($value)
 * @mixin IdeHelperLearnBoxCard
 */
class LearnBoxCard extends KeelearningModel
{
    const TYPE_QUESTION = 0;
    const TYPE_INDEX_CARD = 1;
    const LIMITS = [
        0 * (24 * 60 * 60),
        2 * (24 * 60 * 60),
        7 * (24 * 60 * 60),
        15 * (24 * 60 * 60),
        30 * (24 * 60 * 60),
    ];

    protected $casts = [
        'userdata' => 'json',
        'user_id' => 'integer',
        'foreign_id' => 'integer',
        'type' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Checks if this learn box card is for the given app id.
     *
     * @param $appId
     *
     * @return bool
     */
    public function isForApp($appId)
    {
        switch ($this->type) {
            case self::TYPE_QUESTION:
                return Question::find($this->foreign_id)->app_id == $appId;
            case self::TYPE_INDEX_CARD:
                $indexCard = IndexCard::find($this->foreign_id);
                if (! $indexCard) {
                    return false;
                }

                return $indexCard->app_id == $appId;
        }

        return false;
    }
}
