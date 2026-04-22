<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    protected $fillable = [
        'journal_id',
        'account_id',
        'debit',
        'credit',
        'running_balance',  // ← tambah
        'helper_code',      // ← tambah
        'line_description',
        'line_number',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Journal parent
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Relationship: Account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope: Get debit lines only
     */
    public function scopeDebit($query)
    {
        return $query->where('debit', '>', 0);
    }

    /**
     * Scope: Get credit lines only
     */
    public function scopeCredit($query)
    {
        return $query->where('credit', '>', 0);
    }

    /**
     * Check jika line valid (debit XOR credit, tidak keduanya)
     */
    public function isValid(): bool
    {
        // Harus debit > 0 XOR credit > 0
        return ($this->debit > 0 && $this->credit == 0) || 
               ($this->debit == 0 && $this->credit > 0);
    }

    /**
     * Get amount (baik dari debit atau credit)
     */
    public function getAmount(): float
    {
        return max($this->debit, $this->credit);
    }

    /**
     * Get side (debit atau credit)
     */
    public function getSide(): string
    {
        return $this->debit > 0 ? 'debit' : 'credit';
    }
}
