<?php
namespace App\Http\Middleware;

use App\Models\User;
use App\Services\BudgetService;
use Closure;
use Illuminate\Http\Request;

class AIRBACMiddleware
{
    public function handle(Request $request, Closure $next, BudgetService $budget)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $quota = $user->quotas()->first();
        if (!$quota || $quota->remaining <= 0) {
            return response()->json(['error' => 'AI quota exceeded'], 429);
        }

        $remaining = $budget->getRemainingBudget($user);
        $response = $next($request);
        $response->headers->set('X-AI-Remaining-Daily', (string) $remaining['daily']);
        return $response;
    }
}
