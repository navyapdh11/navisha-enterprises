<?php
namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RecommendationService
{
    public function getRecommendations(string $zone, ?string $festival = null, array $history = []): array
    {
        $cacheKey = "recs_{$zone}_" . md5(serialize([$festival, $history]));
        return Cache::remember($cacheKey, 300, function () use ($zone, $festival, $history) {
            $query = ProductVariant::with(['product', 'inventory'])
                ->whereHas('product', fn($q) => $q->where('status', 'active'))
                ->whereHas('inventory', fn($q) => $q->whereRaw('stock_qty > reserved_stock'));

            // Zone-based filtering
            if ($zone) {
                $query->whereHas('product', fn($q) => $q->where('zones', 'like', "%{$zone}%"));
            }

            // Festival boost
            if ($festival) {
                $query->whereHas('product', fn($q) => $q->where('festivals', 'like', "%{$festival}%"));
            }

            // History collaborative filtering
            if (!empty($history)) {
                $historyIds = collect($history)->pluck('id')->filter()->toArray();
                if (!empty($historyIds)) {
                    $query->whereHas('product', fn($q) => $q->whereNotIn('id', $historyIds));
                }
            }

            return $query->take(12)->get()->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->product->name,
                'sku' => $v->sku,
                'price' => $v->price,
                'image_url' => $v->image_url ?? $v->product->images[0] ?? null,
                'festival' => $festival,
            ])->toArray();
        });
    }

    public function callLaravelInternalAPI(array $data): array
    {
        // Called by AI service - returns real products
        return $this->getRecommendations(
            $data['zone'] ?? '',
            $data['festival'] ?? null,
            $data['history'] ?? []
        );
    }
}
