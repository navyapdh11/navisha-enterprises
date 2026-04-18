<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class BudgetService
{
    public function checkAndConsume(User $user, float $estimatedCost): bool
    {
        $lockKey = "budget_lock_{$user->id}";
        return Cache::lock($lockKey, 5)->get(function () use ($user, $estimatedCost) {
            $quota = $user->quotas()->firstOrCreate([], [
                'daily_limit' => 100,
                'monthly_limit' => 3000,
                'daily_usage' => 0,
                'monthly_usage' => 0,
                'plan_tier' => 'free',
            ]);

            if ($quota->daily_usage + $estimatedCost > $quota->daily_limit) {
                return false;
            }
            if ($quota->monthly_usage + $estimatedCost > $quota->monthly_limit) {
                return false;
            }

            $quota->increment('daily_usage', $estimatedCost);
            $quota->increment('monthly_usage', $estimatedCost);
            return true;
        });
    }

    public function getRemainingBudget(User $user): array
    {
        $quota = $user->quotas()->first();
        if (!$quota) return ['daily' => 0, 'monthly' => 0];
        return [
            'daily' => max(0, $quota->daily_limit - $quota->daily_usage),
            'monthly' => max(0, $quota->monthly_limit - $quota->monthly_usage),
        ];
    }
}
