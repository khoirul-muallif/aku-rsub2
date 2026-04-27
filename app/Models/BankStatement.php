<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatement extends Model
{
    protected $fillable = [
        'account_id', 'period_year', 'period_month',
        'transaction_date', 'description',
        'debit', 'credit', 'balance', 'is_matched',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit'      => 'decimal:2',
        'credit'     => 'decimal:2',
        'balance'    => 'decimal:2',
        'is_matched' => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}