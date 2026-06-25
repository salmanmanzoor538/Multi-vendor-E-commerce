<?php

namespace App\Http\Requests\Customer;

use App\Enums\AddressType;
use App\Models\CustomerAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $address = $this->route('address');

        return $this->user() !== null
            && $address instanceof CustomerAddress
            && $this->user()->can('update', $address);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_column(AddressType::cases(), 'value'))],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s().]+$/'],
            'line_1' => ['required', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:40'],
            'country_code' => ['required', 'string', 'size:2', 'alpha'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => is_string($this->full_name) ? trim($this->full_name) : $this->full_name,
            'phone' => is_string($this->phone) ? trim($this->phone) : $this->phone,
            'line_1' => is_string($this->line_1) ? trim($this->line_1) : $this->line_1,
            'line_2' => is_string($this->line_2) ? trim($this->line_2) : $this->line_2,
            'city' => is_string($this->city) ? trim($this->city) : $this->city,
            'state' => is_string($this->state) ? trim($this->state) : $this->state,
            'postal_code' => is_string($this->postal_code) ? trim($this->postal_code) : $this->postal_code,
            'country_code' => is_string($this->country_code) ? Str::upper(trim($this->country_code)) : $this->country_code,
            'is_default' => $this->boolean('is_default'),
        ]);
    }
}
