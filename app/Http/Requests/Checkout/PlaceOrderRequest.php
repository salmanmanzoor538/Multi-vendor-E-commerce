<?php

namespace App\Http\Requests\Checkout;

use App\Enums\AddressType;
use App\Enums\PaymentProvider;
use App\Enums\UserRole;
use App\Models\CustomerAddress;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole(UserRole::CUSTOMER);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = (int) $this->user()?->id;

        return [
            'shipping_address_id' => [
                'required',
                'integer',
                Rule::exists(CustomerAddress::class, 'id')
                    ->where(fn ($query) => $query->where('user_id', $userId)->where('type', AddressType::SHIPPING->value)),
            ],
            'billing_address_id' => [
                'required',
                'integer',
                Rule::exists(CustomerAddress::class, 'id')
                    ->where(fn ($query) => $query->where('user_id', $userId)->where('type', AddressType::BILLING->value)),
            ],
            'provider' => ['required', Rule::in(array_column(PaymentProvider::cases(), 'value'))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'provider' => is_string($this->provider) ? strtolower(trim($this->provider)) : $this->provider,
        ]);
    }
}
