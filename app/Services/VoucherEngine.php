<?php

namespace App\Services;

use App\Http\APIError;
use App\Jobs\MaybeSendExpirationReminder;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherCode;
use DB;
use Hackzilla\PasswordGenerator\Generator\HybridPasswordGenerator;
use Illuminate\Support\Carbon;

class VoucherEngine
{
    private $generator;

    /**
     * Create a query for vouchers using filter
     *
     * @param $appId
     * @param null $search
     * @param null $filter
     * @param null $orderBy
     * @param false $descending
     * @return Voucher|\Illuminate\Database\Eloquent\Builder
     */
    public function vouchersFilterQuery($appId, $search = null, $filter = null, $orderBy = null, $descending = false) {
        $vouchersQuery = Voucher::where('app_id', $appId);

        if ($search) {
            $vouchersQuery->where(function ($query) use ($search) {
                $query->whereRaw('name LIKE ?', '%'.escapeLikeInput($search).'%')
                ->orWhere('id', extractHashtagNumber($search));
            });
        }
        if ($filter === 'active') {
            $vouchersQuery->where('archived', 0);
        }
        if ($filter === 'archived') {
            $vouchersQuery->where('archived', 1);
        }

        if ($orderBy) {
            $vouchersQuery->orderBy($orderBy, $descending ? 'desc' : 'asc');
        }

        return $vouchersQuery;
    }

    /**
     * Redeems an existing code.
     *
     * @param $voucherCode
     * @param $user
     * @return bool
     * @throws \Throwable
     */
    public function redeemCode($voucherCode, User $user)
    {
        DB::transaction(function () use ($voucherCode, $user) {
            $tagEngine = new TagEngine();

            $voucherTagIds = $voucherCode->voucher->tags()->pluck('tags.id')->toArray();

            $tagChange = $tagEngine->getValidAdditionalTags($voucherTagIds, $user);

            try {
                if ($tagChange->getAdd()) {
                    $user->tags()->attach($tagChange->getAdd());
                }
                if ($tagChange->getRemove()) {
                    $user->tags()->detach($tagChange->getRemove());
                }

                $voucherCode->user_id = $user->id;
                $voucherCode->cash_in_date = Carbon::now();
                $voucherCode->save();
            } catch (\Exception $e) {
                \Sentry::captureException($e, [
                    'extra' => [
                        'userId' => $user->id,
                        'userTags' => $user->tags->pluck('id'),
                        'tagChangeAdd' => $tagChange->getAdd(),
                        'tagChangeRemove' => $tagChange->getRemove(),
                        'voucherTagIds' => $voucherTagIds,
                    ]
                ]);
            }
        });

        MaybeSendExpirationReminder::dispatchAfterResponse($user);

        return true;
    }

    /**
     * Checks if a voucher code can be used.
     *
     * @param $voucherCode
     * @param $appId
     * @return APIError|bool
     */
    public function validateCode($voucherCode, $appId)
    {
        if (! $voucherCode || $voucherCode->voucher->app_id !== $appId) {
            return new APIError(__('errors.voucher_not_found'));
        }

        if ($voucherCode->user_id || $voucherCode->cash_in_date) {
            return new APIError(__('errors.voucher_in_use'));
        }

        return true;
    }

    /**
     * Creates new codes.
     *
     * @param Voucher $voucher
     * @param $amount
     * @param $appId
     * @param null $predefinedCode
     */
    public function createCodes(Voucher $voucher, $amount, $appId, $predefinedCode = null)
    {
        // Query existing codes
        $existingCodes = VoucherCode::whereHas('voucher', function ($query) use ($appId) {
            $query->where('app_id', $appId);
        })->pluck('code')->all();

        // Fallback if code is set but individual code is required
        if (Voucher::TYPE_MULTIPLE_CODE === $voucher->type) {
            $predefinedCode = null;
        }

        for ($i = 0; $i < $amount; $i++) {
            if ($predefinedCode) {
                $code = $predefinedCode;
            } else {
                $code = $this->generate();
                while (in_array($code, $existingCodes)) {
                    $code = $this->generate();
                }
            }

            $voucherCode = new VoucherCode();
            $voucherCode->voucher_id = $voucher->id;
            $voucherCode->cash_in_date = null;
            $voucherCode->user_id = null;
            $voucherCode->code = $code;
            $voucherCode->save();

            $existingCodes[] = $code;
        }
    }

    /**
     * Generates a random code.
     *
     * @return string
     */
    public function generate()
    {
        if (! $this->generator) {
            $this->generator = new HybridPasswordGenerator();

            $this->generator
                ->setUppercase(false)
                ->setLowercase()
                ->setNumbers()
                ->setSymbols(false)
                ->setSegmentLength(9)
                ->setSegmentCount(1)
                ->setSegmentSeparator('-');
        }

        return $this->generator->generatePassword();
    }
}
