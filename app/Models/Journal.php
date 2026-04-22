<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'journal_number',
        'journal_date',
        'period_year',
        'period_month',
        'reference_type',
        'reference_number',
        'memo',
        'description',
        'unit_code',
        'budget_code',
        'transaction_code',
        'status',
        'posted_at',
        'posted_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'total_debit',
        'total_credit',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'posted_at' => 'datetime',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Journal detail lines
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class)->orderBy('line_number');
    }

    /**
     * Relationship: User yang post
     */
    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Relationship: User yang approve
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Get journals dari period tertentu
     */
    public function scopeByPeriod($query, int $year, int $month)
    {
        return $query->where('period_year', $year)
                     ->where('period_month', $month);
    }

    /**
     * Scope: Get journals by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get journals yang sudah posted
     */
    public function scopePosted($query)
    {
        return $query->where('status', 'posted')->whereNotNull('posted_at');
    }

    /**
     * Scope: Get journals dengan detail lines
     */
    public function scopeWithLines($query)
    {
        return $query->with('lines.account');
    }

    /**
     * Scope: Get journals by date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('journal_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Get journals by unit
     */
    public function scopeByUnit($query, string $unitCode)
    {
        return $query->where('unit_code', $unitCode);
    }

    /**
     * Check jika journal sudah balanced (debit = kredit)
     */
    public function isBalanced(): bool
    {
        return (float) $this->total_debit === (float) $this->total_credit;
    }

    /**
     * Get balance difference (untuk validation)
     */
    public function getBalanceDifference(): float
    {
        return (float) $this->total_debit - (float) $this->total_credit;
    }

    /**
     * Post journal (transition dari draft ke posted)
     * CRITICAL: Harus validate sebelum panggil method ini!
     */
    public function markAsPosted($userId): bool
    {
        // Validate double-entry balance
        if (!$this->isBalanced()) {
            return false;
        }

        // Validate tidak ada header accounts
        foreach ($this->lines as $line) {
            if (!$line->account->canPost()) {
                return false;
            }
        }

        // Update journal status
        $this->status = 'posted';
        $this->posted_at = now();
        $this->posted_by = $userId;
        
        return $this->save();
    }

    /**
     * Reverse journal (buat reversal entry, tidak delete)
     */
    public function reverse($userId): ?Journal
    {
        if ($this->status !== 'posted') {
            return null;
        }

        // Create reversal journal dengan nomor baru
        $reversalJournal = Journal::create([
            'journal_number' => $this->generateJournalNumber(),
            'journal_date' => now()->toDateString(),
            'period_year' => now()->year,
            'period_month' => now()->month,
            'reference_type' => 'reversal',
            'reference_number' => $this->journal_number,
            'memo' => "Reversal of {$this->journal_number}",
            'description' => "Reversal entry for: {$this->description}",
            'unit_code' => $this->unit_code,
            'budget_code' => $this->budget_code,
            'transaction_code' => $this->transaction_code,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => $userId,
        ]);

        // Create reversal lines (debit ↔ kredit terbalik)
        $totalDebit = 0;
        $totalCredit = 0;
        $lineNumber = 1;

        foreach ($this->lines as $originalLine) {
            JournalLine::create([
                'journal_id' => $reversalJournal->id,
                'account_id' => $originalLine->account_id,
                'debit' => $originalLine->credit,  // Balik
                'credit' => $originalLine->debit,  // Balik
                'line_description' => "Reversal: {$originalLine->line_description}",
                'line_number' => $lineNumber++,
            ]);

            $totalDebit += $originalLine->credit;
            $totalCredit += $originalLine->debit;
        }

        // Update reversal journal totals
        $reversalJournal->update([
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ]);

        // Update original journal status
        $this->status = 'reversed';
        $this->save();

        return $reversalJournal;
    }

    /**
     * Generate unique journal number
     * Format: JNL-YYYY-000001
     */
    public static function generateJournalNumber(): string
    {
        $year = now()->year;
        $latestNumber = Journal::where('journal_number', 'like', "JNL-{$year}-%")
            ->latest('id')
            ->first();

        $nextNumber = 1;
        if ($latestNumber) {
            $parts = explode('-', $latestNumber->journal_number);
            $nextNumber = intval(end($parts)) + 1;
        }

        return sprintf('JNL-%d-%06d', $year, $nextNumber);
    }

    /**
     * Recalculate total debit & credit dari lines
     */
    public function recalculateTotals(): void
    {
        $this->total_debit = $this->lines()->sum('debit');
        $this->total_credit = $this->lines()->sum('credit');
        $this->save();
    }
}
