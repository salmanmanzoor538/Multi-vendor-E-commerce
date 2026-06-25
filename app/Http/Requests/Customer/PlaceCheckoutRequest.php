<?php

namespace App\Http\Requests\Customer;

use App\Enums\AddressType;
use App\Enums\UserRole;
use App\Models\CustomerAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole(UserRole::CUSTOMER);
    }

    /**
     * @return array<string, mixed>
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
        ];
    }
}
