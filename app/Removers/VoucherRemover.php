<?php

namespace App\Removers;

class VoucherRemover extends Remover
{
    /**
     * Deletes the object's dependees.
     */
    protected function deleteDependees()
    {
        $this->object->tags()->detach();
        $this->object->voucherCodes()->delete();
    }

    /**
     * Checks if voucher codes are already used.
     */
    public function getBlockingDependees()
    {
        $messages = [];
        $vouchers = $this->object
            ->voucherCodes()
            ->whereNotNull('user_id')
            ->whereNotNull('cash_in_date')
            ->get();

        foreach ($vouchers as $voucher) {
            $messages[] = 'Code ('.$voucher->code.') wurde bereits benutzt';
        }

        return count($messages) ? $messages : false;
    }
}
