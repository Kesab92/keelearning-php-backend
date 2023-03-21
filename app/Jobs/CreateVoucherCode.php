<?php

namespace App\Jobs;

use App\Models\VoucherCode;
use App\Services\QueuePriority;
use App\Services\VoucherEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateVoucherCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var null
     */
    protected $appId = null;

    /**
     * @var null
     */
    protected $voucher = null;

    /**
     * @var null
     */
    protected $voucherEngine = null;

    /**
     * @var int
     */
    protected $amount = 0;

    /**
     * @var null
     */
    protected $code = null;

    /**
     * Create a new job instance.
     *
     * @param $voucher
     * @param $amount
     * @param $appId
     * @param null $code
     */
    public function __construct($voucher, $amount, $appId, $code = null)
    {
        $this->voucherEngine = new VoucherEngine();
        $this->voucher = $voucher;
        $this->amount = $amount;
        $this->appId = $appId;
        $this->code = $code;
        $this->queue = QueuePriority::HIGH;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->voucherEngine->createCodes($this->voucher, $this->amount, $this->appId, $this->code);
    }
}
