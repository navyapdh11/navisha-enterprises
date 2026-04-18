<?php
namespace App\Http\Middleware;

use App\Models\ComplianceLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ComplianceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $platformNumber = config('compliance.platform_number', 'NP-2026-001');
        $sessionId = $request->header('X-Session-ID', 'unknown');

        $logData = [
            'entity_type' => 'request',
            'entity_id' => $request->path(),
            'compliance_type' => 'data_privacy',
            'platform_number' => $platformNumber,
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'pass',
            'details' => json_encode(['method' => $request->method(), 'path' => $request->path()]),
        ];

        try {
            dispatch(function () use ($logData) {
                ComplianceLog::create($logData);
            })->onQueue('low');
        } catch (\Exception $e) {
            // Fallback: log synchronously (fix #2)
            ComplianceLog::create($logData);
            Log::warning('Compliance log fell back to sync: ' . $e->getMessage());
        }

        return $response;
    }
}
