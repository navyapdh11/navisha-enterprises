<?php
namespace App\Http\Controllers;

use App\Services\PaymentOrchestrator;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentCallbackController extends Controller
{
    public function __invoke(string $gateway, Request $request, PaymentOrchestrator $payment, InventoryService $inventory): JsonResponse
    {
        $data = $request->all();
        $success = $payment->handleCallback($gateway, $data);
        return response()->json(['success' => $success, 'status' => $success ? 'paid' : 'failed']);
    }
}
