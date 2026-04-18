<?php
namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    public function recommend(Request $request, RecommendationService $service): JsonResponse
    {
        $validated = $request->validate([
            'zone' => 'required|string',
            'festival' => 'nullable|string',
            'history' => 'nullable|array',
        ]);
        $products = $service->getRecommendations($validated['zone'], $validated['festival'] ?? null, $validated['history'] ?? []);
        return response()->json(['products' => $products]);
    }
}
