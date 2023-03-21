<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Traits\PersonalData;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Str;
use View;

class VouchersController extends Controller
{
    use PersonalData;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:vouchers,vouchers-edit');
        $this->personalDataRightsMiddleware('vouchers');
        View::share('activeNav', 'vouchers');
    }

    /**
     * Shows the index page of all vouchers.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('vue-component', [
            'hasFluidContent' => false,
            'component' => 'routed',
            'props' => [
                'has_candy_frontend' => $this->appSettings->getValue('has_candy_frontend'),
            ],
        ]);
    }

    /**
     * Downloads not used codes of given id.
     * @param $id
     * @throws \Exception
     */
    public function downloadVoucherCodes($id)
    {
        $voucher = Voucher::with('voucherCodes')->find($id);
        if (! $voucher || $voucher->app_id !== appId()) {
            app()->abort(404);
        }

        $unusedCodes = $voucher->voucherCodes
            ->filter(function ($code) {
                return ! $code->cash_in_date && ! $code->user_id;
            });

        $usedCodes = $voucher->voucherCodes
            ->filter(function ($code) {
                return $code->cash_in_date && $code->user_id;
            });

        $data = [
            'unusedCodes' => $unusedCodes,
            'usedCodes' => $usedCodes,
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
        ];
        $filename = Str::slug($voucher->name).'-voucher-codes-'.Carbon::now()->format('d.m.Y-H:i').'.xlsx';

        return Excel::download(new DefaultExport($data, 'vouchers.csv.voucher-codes'), $filename);
    }
}
