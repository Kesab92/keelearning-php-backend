<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Services\VoucherEngine;
use Illuminate\Http\Request;

class VouchersController extends Controller
{
    /**
     * Returns all vouchers by authenticated user.
     * @throws \Exception
     */
    public function findVouchersByAuthenticatedUser()
    {
        $voucherCodes = $codes = VoucherCode::whereHas('voucher', function ($query) {
            $query->where('app_id', user()->app_id);
        })->where('user_id', user()->id)
            ->get()
            ->map(function (VoucherCode $item) {
                $validUntil = $item->getEndDate();
                if ($validUntil) {
                    $validUntil = $validUntil->toDateString();
                }

                return [
                  'code' => $item->code,
                  'name' => $item->voucher->name,
                  'cash_in_date' => $item->cash_in_date->toDateString(),
                  'valid_until' => $validUntil,
                ];
            });

        return \Response::json([
            'success' => true,
            'data' => $voucherCodes,
        ]);
    }

    /**
     * Redeems given code.
     * @param Request $request
     * @param VoucherEngine $voucherEngine
     * @return APIError
     * @throws \Exception
     */
    public function redeemCode(Request $request, VoucherEngine $voucherEngine)
    {
        $this->validate($request, [
           'code' => 'required',
        ]);

        $voucherCode = VoucherCode::where('code', $request->input('code'))
            ->whereHas('voucher', function ($query) {
                $query->where('app_id', user()->app_id)
                    ->where('archived', 0);
            })
            ->first();

        if (! $voucherCode) {
            return new APIError(__('errors.voucher_not_found'));
        }

        if ($voucherCode->voucher->type == Voucher::TYPE_SINGLE_CODE) {
            $codeInUse = VoucherCode::where('code', $request->input('code'))
                ->whereHas('voucher', function ($query) {
                    $query->where('app_id', user()->app_id);
                })
                ->whereNotNull('cash_in_date')
                ->where('user_id', user()->id)
                ->count();

            if ($codeInUse) {
                return new APIError(__('errors.voucher_in_use'));
            }

            $voucherCode = VoucherCode::where('code', $request->input('code'))
                ->whereHas('voucher', function ($query) {
                    $query->where('app_id', user()->app_id)
                        ->where('archived', 0);
                })
                ->whereNull('cash_in_date')
                ->whereNull('user_id')
                ->first();
        }

        $validationResult = $voucherEngine->validateCode($voucherCode, user()->app_id);
        if ($validationResult instanceof APIError) {
            return $validationResult;
        }

        $success = $voucherEngine->redeemCode($voucherCode, user());

        return \Response::json([
            'success' => $success,
        ]);
    }
}
