<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.sku' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'zone' => 'required|string',
            'preferred_gateway' => 'nullable|string|in:esewa,khalti,cod',
            'idempotency_key' => 'required|string|unique:orders,idempotency_key',
        ];
    }
}
