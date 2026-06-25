<?php

namespace App\Http\Requests\Cart;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemQuantityRequest extends FormRequest
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
            'quantity' => [
                'required',
                'integer',
                'min:'.((int) config('cart.quantity.min', 1)),
                'max:'.((int) config('cart.quantity.max', 99)),
            ],
        ];
    }
}
