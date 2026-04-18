<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentOrchestrator
{
    private const CIRCUIT_BREAKER_TTL = 60;
    private const FAILURE_THRESHOLD = 5;
    
    public function process(Order $order, string $gateway = 'esewa'): array
    {
        if ($this->isCircuitOpen($gateway)) {
            Log::warning("Circuit breaker open for {$gateway}, falling back to COD");
            return ['success' => false, 'fallback' => 'cod', 'reason' => 'Gateway unavailable'];
        }

        try {
            match ($gateway) {
                'esewa' => $result = $this->processESewa($order),
                'khalti' => $result = $this->processKhalti($order),
                'cod' => $result = $this->processCOD($order),
                default => $result = ['success' => false, 'fallback' => 'cod'],
            };
            
            if ($result['success']) {
                $this->recordSuccess($gateway);
            } else {
                $this->recordFailure($gateway);
            }
            return $result;
        } catch (\Exception $e) {
            $this->recordFailure($gateway);
            return ['success' => false, 'fallback' => 'cod', 'error' => $e->getMessage()];
        }
    }

    private function processESewa(Order $order): array
    {
        $response = Http::timeout(10)->post(config('services.esewa.api_url'), [
            'amount' => $order->total_amount,
            'tax_amount' => 0,
            'product_service_charge' => 0,
            'product_delivery_charge' => 0,
            'total_amount' => $order->total_amount,
            'transaction_uuid' => $order->idempotency_key,
            'product_code' => config('services.esewa.product_code'),
            'success_url' => route('payment.callback', ['gateway' => 'esewa']),
            'failure_url' => route('payment.failure'),
        ]);
        
        if ($response->successful()) {
            return ['success' => true, 'redirect_url' => $response->json('redirect_url') ?? config('services.esewa.payment_url')];
        }
        return ['success' => false, 'error' => 'eSewa payment initiation failed'];
    }

    private function processKhalti(Order $order): array
    {
        $response = Http::withHeaders(['Authorization' => 'Key ' . config('services.khalti.secret_key')])
            ->timeout(10)
            ->post(config('services.khalti.api_url'), [
                'return_url' => route('payment.callback', ['gateway' => 'khalti']),
                'website_url' => config('app.url'),
                'amount' => $order->total_amount * 100,
                'purchase_order_id' => $order->idempotency_key,
                'purchase_order_name' => $order->order_number,
            ]);
        
        if ($response->successful()) {
            return ['success' => true, 'redirect_url' => $response->json('payment_url')];
        }
        return ['success' => false, 'error' => 'Khalti payment initiation failed'];
    }

    private function processCOD(Order $order): array
    {
        return ['success' => true, 'method' => 'cod'];
    }

    public function handleCallback(string $gateway, array $data): bool
    {
        $order = Order::where('idempotency_key', $data['transaction_uuid'] ?? '')->first();
        if (!$order) return false;
        if ($order->payment_status === 'paid') return true; // idempotent

        $isPaid = match ($gateway) {
            'esewa' => ($data['response_code'] ?? '') === 'COMPLETE',
            'khalti' => ($data['status'] ?? '') === 'Completed',
            default => false,
        };

        if ($isPaid) {
            $order->update(['payment_status' => 'paid', 'gateway' => $gateway, 'payment_reference' => $data['idx'] ?? $data['transaction_id'] ?? null]);
            app(InventoryService::class)->confirmReservation($order);
        } else {
            $order->update(['payment_status' => 'failed']);
            app(InventoryService::class)->releaseReservation($order);
        }
        return $isPaid;
    }

    private function isCircuitOpen(string $gateway): bool
    {
        $failures = Cache::get("payment_failures_{$gateway}", 0);
        return $failures >= self::FAILURE_THRESHOLD;
    }

    private function recordFailure(string $gateway): void
    {
        $key = "payment_failures_{$gateway}";
        Cache::add($key, 0, self::CIRCUIT_BREAKER_TTL);
        Cache::increment($key);
    }

    private function recordSuccess(string $gateway): void
    {
        Cache::forget("payment_failures_{$gateway}");
    }
}
