<?php

namespace App\Models;

/**
 * App\Models\FcmToken
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string|null $app_store_id
 * @property string|null $platform
 * @property string|null $model
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereAppStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FcmToken whereUserId($value)
 * @mixin IdeHelperFcmToken
 */
class FcmToken extends KeelearningModel
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
