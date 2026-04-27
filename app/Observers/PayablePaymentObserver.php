<?php

namespace App\Observers;

use App\Models\PayablePayment;

class PayablePaymentObserver
{
    
    public function created(PayablePayment $payment): void
    {
        $payment->payable->refresh()->updatePaidAmount();
    }

    public function updated(PayablePayment $payment): void
    {
        $payment->payable->refresh()->updatePaidAmount();
    }

    public function deleted(PayablePayment $payment): void
    {
        $payment->payable->refresh()->updatePaidAmount();
    }

}
