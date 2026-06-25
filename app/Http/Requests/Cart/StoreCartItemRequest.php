<?php

namespace App\Http\Requests\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
            'product_variant_id' => ['nullable', 'integer', Rule::exists(ProductVariant::class, 'id')],
            'quantity' => [
                'required',
                'integer',
                'min:'.((int) config('cart.quantity.min', 1)),
                'max:'.((int) config('cart.quantity.max', 99)),
            ],
        ];
    }
}
