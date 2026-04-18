<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAIQuota extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'feature',
        'quota_limit',
        'quota_used',
        'period_start',
        'period_end',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quota_limit' => 'integer',
        'quota_used' => 'integer',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns this quota record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the remaining quota.
     */
    public function getRemainingAttribute(): int
    {
        return max(0, $this->quota_limit - $this->quota_used);
    }

    /**
     * Check if the user has exceeded their quota.
     */
    public function hasQuotaExceeded(): bool
    {
        return $this->quota_used >= $this->quota_limit;
    }

    /**
     * Increment the quota usage.
     */
    public function incrementUsage(int $amount = 1): void
    {
        $this->increment('quota_used', $amount);
    }

    /**
     * Check if the quota period is still valid.
     */
    public function isPeriodValid(): bool
    {
        if ($this->period_end === null) {
            return true;
        }

        return now()->lte($this->period_end);
    }

    /**
     * Scope a query to only include active quotas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include currently valid periods.
     */
    public function scopeValidPeriod($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('period_end')
                  ->orWhere('period_end', '>=', now());
        });
    }
}
