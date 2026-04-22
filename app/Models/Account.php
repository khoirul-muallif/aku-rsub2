<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'parent_id',
        'current_balance',
        'is_active',
        'is_header',
        'normal_side',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_header' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Parent account (untuk hierarchy)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Relationship: Child accounts
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Relationship: Journal lines yang posting ke akun ini
     */
    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * Get full account hierarchy path
     * Contoh: "Aktiva Lancar > Kas > Kas Bendahara"
     */
    public function getHierarchyPathAttribute(): string
    {
        $path = [$this->name];
        $account = $this;
        
        while ($account->parent) {
            $account = $account->parent;
            array_unshift($path, $account->name);
        }
        
        return implode(' > ', $path);
    }

    /**
     * Scope: Get only active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get only non-header accounts (yang bisa posting)
     */
    public function scopePosting($query)
    {
        return $query->where('is_header', false);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Get account with current period balance
     */
    public function scopeWithBalance($query, int $year, int $month)
    {
        return $query->with(['balances' => function ($q) use ($year, $month) {
            $q->where('period_year', $year)
              ->where('period_month', $month);
        }]);
    }

    /**
     * Get account balance snapshots
     */
    public function balances(): HasMany
    {
        return $this->hasMany(AccountBalance::class);
    }

    /**
     * Get COA version changes
     */
    public function versions(): HasMany
    {
        return $this->hasMany(CoaVersion::class);
    }

    /**
     * Check if account dapat posting (not header)
     */
    public function canPost(): bool
    {
        return !$this->is_header && $this->is_active;
    }
}
