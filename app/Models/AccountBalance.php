<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Account Balance Snapshots (per month)
 */
class AccountBalance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'period_year',
        'period_month',
        'opening_balance',
        'debit_amount',
        'credit_amount',
        'closing_balance',
        'updated_at',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'updated_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope: Get balance for specific period
     */
    public function scopeByPeriod($query, int $year, int $month)
    {
        return $query->where('period_year', $year)
                     ->where('period_month', $month);
    }

    /**
     * Calculate closing balance = opening + debit - credit
     */
    public function calculateClosingBalance(): float
    {
        return (float) $this->opening_balance + 
               (float) $this->debit_amount - 
               (float) $this->credit_amount;
    }
}

/**
 * COA Version Changes Tracking
 */
class CoaVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'period_year',
        'period_month',
        'old_name',
        'new_name',
        'old_description',
        'new_description',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope: Get changes untuk specific period
     */
    public function scopeByPeriod($query, int $year, int $month)
    {
        return $query->where('period_year', $year)
                     ->where('period_month', $month);
    }
}

/**
 * Audit Log - Mandatory untuk Compliance
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'table_name',
        'record_id',
        'action',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get audit logs untuk specific table
     */
    public function scopeTable($query, string $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Scope: Get audit logs untuk specific record
     */
    public function scopeRecord($query, int $recordId)
    {
        return $query->where('record_id', $recordId);
    }

    /**
     * Scope: Get audit logs by action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Get logs untuk specific user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get logs dalam date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Log access change (create entry ke audit_logs)
     */
    public static function logChange(
        string $tableName,
        int $recordId,
        string $action,
        ?int $userId,
        array $oldValues = [],
        array $newValues = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return static::create([
            'table_name' => $tableName,
            'record_id' => $recordId,
            'action' => $action,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    /**
     * Get what changed (comparison old vs new)
     */
    public function getChangedFields(): array
    {
        $changed = [];
        $oldValues = (array) $this->old_values;
        $newValues = (array) $this->new_values;

        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changed[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changed;
    }
}
