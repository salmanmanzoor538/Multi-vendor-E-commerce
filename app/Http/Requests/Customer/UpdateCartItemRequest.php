<?php

namespace App\Http\Requests\Customer;

use App\Models\CartItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cartItem = $this->route('cartItem');

        return $this->user() !== null
            && $cartItem instanceof CartItem
            && $this->user()->can('update', $cartItem);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:'.(int) config('cart.quantity.max', 99)],
        ];
    }
}
