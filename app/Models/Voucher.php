<?php

namespace App\Models;

/**
 * App\Models\Voucher
 *
 * @property int $id
 * @property string $name
 * @property int $amount
 * @property int $app_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $type
 * @property int $validity_interval
 * @property int|null $validity_duration
 * @property-read mixed $current_amount
 * @property-read mixed $selected_tags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VoucherCode[] $voucherCodes
 * @property-read int|null $voucher_codes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher query()
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereValidityDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Voucher whereValidityInterval($value)
 * @mixin IdeHelperVoucher
 */
class Voucher extends KeelearningModel
{
    use \App\Traits\Saferemovable;

    const TYPE_MULTIPLE_CODE = 0;
    const TYPE_SINGLE_CODE = 1;

    const INTERVAL_MONTHS = 0;
    const INTERVAL_DAYS = 1;

    protected $appends = [
        'selectedTags',
        'currentAmount',
    ];

    protected $hidden = [
        'tags',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voucherCodes()
    {
        return $this->hasMany(VoucherCode::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'voucher_tags')->withTimestamps();
    }

    /**
     * Gets assigned tags.
     * @return mixed
     */
    public function getSelectedTagsAttribute()
    {
        return $this->tags->pluck('id');
    }

    public function getCurrentAmountAttribute()
    {
        $count = $this->voucherCodes()
            ->whereNull('user_id')
            ->whereNull('cash_in_date')
            ->count();

        return $count;
    }
}
