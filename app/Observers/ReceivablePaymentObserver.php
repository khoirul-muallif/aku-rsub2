<?php

namespace App\Observers;

use App\Models\ReceivablePayment;

class ReceivablePaymentObserver
{
    
    public function created(ReceivablePayment $payment): void
    {
        $payment->receivable->refresh()->updatePaidAmount();
    }

    public function updated(ReceivablePayment $payment): void
    {
        $payment->receivable->refresh()->updatePaidAmount();
    }

    public function deleted(ReceivablePayment $payment): void
    {
        $payment->receivable->refresh()->updatePaidAmount();
    }
 
}
