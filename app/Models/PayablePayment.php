<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayablePayment extends Model
{
    protected $fillable = [
        'payable_id', 'journal_id', 'amount',
        'paid_date', 'payment_method', 'reference_number', 'notes',
    ];

    protected $casts = [
        'paid_date' => 'date',
        'amount'    => 'decimal:2',
    ];

    public function payable(): BelongsTo
    {
        return $this->belongsTo(Payable::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}