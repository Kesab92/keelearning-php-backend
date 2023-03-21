<?php

namespace App\Models;

use Illuminate\Support\Carbon;

/**
 * App\Models\VoucherCode
 *
 * @property int $id
 * @property int $voucher_id
 * @property string $code
 * @property int|null $user_id
 * @property Carbon|null $cash_in_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Voucher $voucher
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereCashInDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VoucherCode whereVoucherId($value)
 * @mixin IdeHelperVoucherCode
 */
class VoucherCode extends KeelearningModel
{
    protected $dates = [
        'created_at',
        'updated_at',
        'cash_in_date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the date until this voucher is valid
     *
     * @return null|Carbon
     */
    public function getEndDate()
    {
        if(!$this->voucher->validity_duration) {
            return null;
        }
        if($this->voucher->validity_interval === Voucher::INTERVAL_MONTHS) {
            return $this->cash_in_date->addMonths($this->voucher->validity_duration);
        }
        if($this->voucher->validity_interval === Voucher::INTERVAL_DAYS) {
            return $this->cash_in_date->addDays($this->voucher->validity_duration);
        }
        \Sentry::captureMessage('Invalid validity interval for voucher code ' . $this->id);
        return null;
    }
}
