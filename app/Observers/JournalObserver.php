<?php

namespace App\Observers;

use App\Models\Journal;

class JournalObserver
{
 

    /**
     * Handle the Journal "updated" event.
     */
    public function updated(Journal $journal): void
    {
        // Kalau status berubah jadi posted, recalculate running balance
        if ($journal->isDirty('status') && $journal->status === 'posted') {
            $accountIds = $journal->lines->pluck('account_id')->unique();
            foreach ($accountIds as $accountId) {
                Journal::recalculateRunningBalance($accountId);
            }
        }
    }

}
