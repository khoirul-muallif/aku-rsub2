<?php

namespace App\Providers;

use App\Models\Journal;
use App\Observers\JournalObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\ReceivablePayment;
use App\Models\PayablePayment;
use App\Observers\ReceivablePaymentObserver;
use App\Observers\PayablePaymentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Journal::observe(JournalObserver::class);
        ReceivablePayment::observe(ReceivablePaymentObserver::class);
        PayablePayment::observe(PayablePaymentObserver::class);
    }
}
