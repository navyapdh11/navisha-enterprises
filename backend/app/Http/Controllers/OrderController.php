<?php
namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use App\Services\PaymentOrchestrator;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function checkout(CheckoutRequest $request, PaymentOrchestrator $payment, InventoryService $inventory): JsonResponse
    {
        $validated = $request->validated();

        // 1. Idempotency check
        $existing = Order::where('idempotency_key', $validated['idempotency_key'])->first();
        if ($existing) {
            return response()->json(['order' => $existing, 'redirect_url' => $this->getPaymentRedirectUrl($existing)]);
        }

        // 2. Create order with idempotency key
        $order = Order::create([
            'order_number' => 'ORD-' . uniqid(),
            'user_id' => auth()->id(),
            'total_amount' => $validated['total_amount'],
            'zone' => $validated['zone'],
            'idempotency_key' => $validated['idempotency_key'],
            'payment_status' => 'pending',
        ]);

        // 3. Pre-check stock
        foreach ($validated['items'] as $item) {
            $variant = ProductVariant::where('sku', $item['sku'])->first();
            if (!$variant) { $order->delete(); return response()->json(['error' => "SKU {$item['sku']} not found"], 404); }
            $inv = $variant->inventory;
            if (!$inv || $inv->stock_qty < $item['quantity']) { $order->delete(); return response()->json(['error' => "Out of stock for {$item['sku']}"], 409); }
        }

        // 4. Reserve stock
        if (!$inventory->reserveStockDB($order, $validated['items'])) { $order->delete(); return response()->json(['error' => 'Stock reservation failed'], 409); }

        try {
            $result = $payment->process($order, $validated['preferred_gateway'] ?? 'esewa');
            if ($result['success']) {
                $inventory->confirmReservation($order);
                return response()->json(['redirect_url' => $result['redirect_url'] ?? null, 'order' => $order]);
            } elseif (($result['fallback'] ?? '') === 'cod') {
                $order->update(['payment_status' => 'cod']);
                return response()->json(['message' => 'Order placed with COD', 'order' => $order]);
            }
            $inventory->releaseReservation($order);
            $order->delete();
            return response()->json(['error' => 'Payment failed'], 502);
        } catch (\Exception $e) {
            $inventory->releaseReservation($order);
            $order->delete();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleCallback(string $gateway, PaymentOrchestrator $payment): JsonResponse
    {
        $data = request()->all();
        $success = $payment->handleCallback($gateway, $data);
        return response()->json(['success' => $success]);
    }

    private function getPaymentRedirectUrl(Order $order): ?string
    {
        return $order->payment_status === 'pending' ? route('payment.redirect', ['order' => $order->id]) : null;
    }
}
