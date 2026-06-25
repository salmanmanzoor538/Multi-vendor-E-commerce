<?php

namespace App\Http\Requests\Customer;

use App\Enums\ProductStatus;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', CartItem::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists(Product::class, 'id')->where(fn ($query) => $query->where('status', ProductStatus::ACTIVE->value)),
            ],
            'product_variant_id' => [
                'nullable',
                'integer',
                Rule::exists(ProductVariant::class, 'id')->where(
                    fn ($query) => $query->where('product_id', (int) $this->input('product_id'))
                ),
            ],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:'.(int) config('cart.quantity.max', 99)],
        ];
    }
}
