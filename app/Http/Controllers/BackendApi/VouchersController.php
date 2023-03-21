<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Jobs\CreateVoucherCode;
use App\Models\App;
use App\Models\Tag;
use App\Models\TagGroup;
use App\Models\Voucher;
use App\Models\VoucherCode;
use App\Services\VoucherEngine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VouchersController extends Controller
{
    const ORDER_BY = [
        'id',
        'name',
        'validity_duration',
        'amount',
        'type',
        'created_at',
    ];
    const PER_PAGE = [
        50,
        100,
        200,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:vouchers,vouchers-edit');
    }

    /**
     * Get all existing app dependent vouchers.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function index(Request $request, VoucherEngine $voucherEngine)
    {
        $orderBy = $request->input('sortBy');
        if (! in_array($orderBy, self::ORDER_BY)) {
            $orderBy = self::ORDER_BY[0];
        }
        $orderDescending = $request->input('descending') === 'true';
        $page = (int) $request->input('page') ?? 1;
        $perPage = $request->input('rowsPerPage');
        if (! in_array($perPage, self::PER_PAGE)) {
            $perPage = self::PER_PAGE[0];
        }
        $filter = $request->input('filter');
        $search = $request->input('search');

        $vouchersQuery = $voucherEngine->vouchersFilterQuery(appId(), $search, $filter, $orderBy, $orderDescending);
        $countVouchers = $vouchersQuery->count();

        $vouchers = $vouchersQuery
            ->with(['tags'])
            ->withCount('voucherCodes')
            ->offset($perPage * ($page - 1))
            ->limit($perPage)
            ->get();
        $vouchers->transform(function ($voucher) {
            $voucher->amount_used = $voucher
                ->voucherCodes()
                ->where(function(Builder $q) {
                    $q->whereNotNull('user_id')
                        ->orWhereNotNull('cash_in_date');
                })->count();

            $voucher->amount_generated = $voucher->voucher_codes_count;

            if ($voucher->type == Voucher::TYPE_SINGLE_CODE && $voucher->voucher_codes_count) {
                $voucher->code = $voucher->voucherCodes()->first()->code;
            }

            return $voucher->only([
                'id',
                'amount',
                'amount_generated',
                'amount_used',
                'code',
                'created_at',
                'selectedTags',
                'name',
                'type',
                'validity_interval',
                'validity_duration',
                'voucher_codes_count',
                'archived',
            ]);
        });

        $data = [
            'count' => $countVouchers,
            'vouchers' => $vouchers,
            'tagsRequired' =>$this->areTagsRequired(),
            'tagGroups' => $this->getTagGroups(),
            'tagsWithoutGroup' => $this->getTagsWithoutGroup(),
        ];

        return \Response::json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * @param $voucherId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function show($voucherId) {
        $voucher = $this->getVoucher($voucherId);

        return \Response::json([
            'voucher' => $this->getVoucherResponse($voucher),
            'tagsRequired' =>$this->areTagsRequired(),
            'tagGroups' => $this->getTagGroups(),
            'tagsWithoutGroup' => $this->getTagsWithoutGroup(),
        ]);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $validationRules = [
            'name' => 'required|min:3',
            'validity_duration' => 'nullable|numeric',
            'validity_interval' => 'nullable|numeric',
            'amount' => 'required|numeric',
        ];

        if (appId() === App::ID_DGQ) {
            $validationRules = array_merge(['selectedTags' => 'required'], $validationRules);
        }
        $this->validate($request, $validationRules);

        if ($request->input('code')) {
            $codeCount = VoucherCode::where('code', $request->input('code'))
                ->whereHas('voucher', function ($query) {
                    $query->where('app_id', appId());
                })
                ->count();

            if ($codeCount > 0) {
                return \Response::json([
                    'success' => false,
                    'error' => 'Code existiert bereits',
                ]);
            }
        }

        $voucher = DB::transaction(function () use ($request) {
            $type = $request->input('type')
                ? Voucher::TYPE_SINGLE_CODE
                : Voucher::TYPE_MULTIPLE_CODE;

            $voucher = new Voucher();
            $voucher->app_id = appId();
            $voucher->type = $type;
            $voucher->name = $request->input('name');
            $voucher->amount = $request->input('amount');
            $voucher->validity_duration = $request->input('validity_duration');
            $voucher->validity_interval = $request->input('validity_interval');
            $voucher->save();

            $voucher->tags()->sync($request->input('selectedTags'));

            return $voucher;
        });

        // Create a new job
        CreateVoucherCode::dispatch(
            $voucher,
            $voucher->amount,
            appId(),
            Voucher::TYPE_MULTIPLE_CODE === $voucher->type ? null : $request->input('code')
        );

        return \Response::json([
            'success' => true,
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|min:3',
            'validity_duration' => 'nullable|numeric',
            'validity_interval' => 'nullable|numeric',
        ]);

        $voucher = $this->getVoucher($id);

        if ($voucher->validity_duration !== $request->input('validity_duration') || $voucher->validity_interval !== $request->input('validity_interval')) {
            $usedVoucherCodes = VoucherCode::where('voucher_id', $id)
                ->whereNotNull('user_id')
                ->count();
            if ($usedVoucherCodes) {
                abort(403, 'Die Gültigkeitsdauer kann nicht geändert werden, da ein Code bereits genutzt wurde.');
            }
        }

        $voucher->name = $request->input('name');
        $voucher->validity_duration = $request->input('validity_duration');
        $voucher->validity_interval = $request->input('validity_interval');
        $voucher->save();

        $voucher->tags()->sync($request->input('selectedTags') ?? []);

        return \Response::json([
            'voucher' => $this->getVoucherResponse($voucher),
            'tagsRequired' =>$this->areTagsRequired(),
            'tagGroups' => $this->getTagGroups(),
            'tagsWithoutGroup' => $this->getTagsWithoutGroup(),
        ]);
    }

    /**
     * Returns dependencies and blockers
     *
     * @param $voucherId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteInformation($voucherId) {
        $voucher = $this->getVoucher($voucherId);

        return \Response::json([
            'dependencies' => $voucher->safeRemoveDependees(),
            'blockers' => $voucher->getBlockingDependees(),
        ]);
    }

    /**
     * Deletes the voucher
     *
     * @param $voucherId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($voucherId) {
        $voucher = $this->getVoucher($voucherId);

        $result = $voucher->safeRemove();

        if($result->isSuccessful()) {
            return \Response::json([], 204);
        } else {
            return \Response::json($result->getMessages(), 400);
        }
    }

    /**
     * Reproduce codes until voucher.amount is reached.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function produce(Request $request, $id)
    {
        $this->validate($request, [
            'amount' => 'required',
        ]);

        $voucher = Voucher::findOrFail($id);
        if ($voucher->app_id !== appId()) {
            abort(403);
        }

        if ($voucher->amount > $request->input('amount')) {
            return \Response::json([
                'success' => false,
                'error' => 'Die Anzahl der Vouchers darf nicht kleiner sein, als die jetzige Anzahl.',
            ]);
        }

        $amount = $request->input('amount') - $voucher->amount;

        $code = null;
        if ($voucher->type === Voucher::TYPE_SINGLE_CODE) {
            $voucherCode = VoucherCode::where('voucher_id', $voucher->id)->first();
            $code = $voucherCode->code;
        }

        // Create a new job
        CreateVoucherCode::dispatch($voucher, $amount, appId(), $code);

        $voucher->amount = $request->input('amount');
        $voucher->save();

        return \Response::json([
            'success' => true,
        ]);
    }

    /**
     * Archives the voucher.
     * @param $voucherId
     * @return \Illuminate\Http\JsonResponse
     */
    public function archive($voucherId)
    {
        $voucher = $this->getVoucher($voucherId);

        $voucher->archived = true;
        $voucher->save();

        return \Response::json([]);
    }

    /**
     * Unarchives the voucher.
     * @param $voucherId
     * @return \Illuminate\Http\JsonResponse
     */
    public function unarchive($voucherId)
    {
        $voucher = $this->getVoucher($voucherId);

        $voucher->archived = false;
        $voucher->save();

        return \Response::json([]);
    }

    private function getVoucher($voucherId) {
        $voucher = Voucher::where('id', $voucherId)
            ->withCount('voucherCodes')
            ->firstOrFail();

        if ($voucher->app_id !== appId()) {
            abort(403);
        }

        return $voucher;
    }

    private function getVoucherResponse(Voucher $voucher) {
        return [
            'id' => $voucher->id,
            'created_at' => $voucher->created_at,
            'name' => $voucher->name,
            'archived' => $voucher->archived,
            'validity_duration' => $voucher->validity_duration,
            'validity_interval' => $voucher->validity_interval,
            'selectedTags' => $voucher->tags->pluck('id'),
            'amount' => $voucher->amount,
            'amount_generated' => $voucher->voucher_codes_count,
        ];
    }

    private function areTagsRequired() {
        return  appId() === App::ID_DGQ;
    }

    private function getTagGroups() {
        return TagGroup
            ::ofApp(appId())
            ->with('tags')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    private function getTagsWithoutGroup() {
        return Tag
            ::ofApp(appId())
            ->orderBy('label')
            ->doesntHave('tagGroup')
            ->get();
    }
}
