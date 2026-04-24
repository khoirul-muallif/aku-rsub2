<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payable extends Model
{
    protected $fillable = [
        'account_id', 'invoice_number', 'creditor_name',
        'invoice_date', 'due_date', 'paid_date',
        'amount', 'paid_amount', 'discount',
        'status', 'notes', 'journal_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'paid_date'    => 'date',
        'amount'       => 'decimal:2',
        'paid_amount'  => 'decimal:2',
        'discount'     => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function getSisaHutangAttribute(): float
    {
        return (float) $this->amount - (float) $this->paid_amount - (float) $this->discount;
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public static function generateInvoiceNumber(): string
    {
        $year   = now()->year;
        $month  = now()->month;
        $latest = static::where('invoice_number', 'like', "HUT-{$year}{$month}%")
            ->latest('id')->first();
        $next = $latest ? intval(substr($latest->invoice_number, -4)) + 1 : 1;
        return sprintf('HUT-%s%02d-%04d', $year, $month, $next);
    }
}