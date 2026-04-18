<?php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\InventoryLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function reserveStockDB(Order $order, array $items): bool
    {
        try {
            DB::transaction(function () use ($order, $items) {
                foreach ($items as $item) {
                    $variant = ProductVariant::where('sku', $item['sku'])->lockForUpdate()->firstOrFail();
                    $inventory = $variant->inventory;
                    $available = $inventory->stock_qty - $inventory->reserved_stock;
                    if ($available < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$item['sku']}");
                    }
                    $inventory->reserved_stock += $item['quantity'];
                    $inventory->save();
                    OrderItem::create([
                        'order_id' => $order->id,
                        'variant_id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $variant->price,
                        'total' => $variant->price * $item['quantity'],
                    ]);
                }
            });
            return true;
        } catch (\Exception $e) {
            Log::error('Stock reservation failed', ['error' => $e->getMessage(), 'order_id' => $order->id ?? null]);
            return false;
        }
    }

    public function confirmReservation(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $inventory = $item->variant->inventory;
                $inventory->stock_qty -= $item->quantity;
                $inventory->reserved_stock -= $item->quantity;
                $inventory->save();
            }
            $order->update(['payment_status' => 'paid']);
        });
    }

    public function releaseReservation(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $inventory = $item->variant->inventory;
                $inventory->reserved_stock -= $item->quantity;
                $inventory->save();
            }
            $order->items()->delete();
            $order->update(['payment_status' => 'failed']);
        });
    }
}
