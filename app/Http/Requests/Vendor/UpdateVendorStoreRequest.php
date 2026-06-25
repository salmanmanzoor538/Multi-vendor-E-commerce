<?php

namespace App\Http\Requests\Vendor;

use App\Models\Vendor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateVendorStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $vendor = $this->user()?->ownedVendor;

        return $this->user() !== null
            && $vendor !== null
            && $this->user()->can('update', $vendor);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vendorId = $this->user()?->ownedVendor?->id;

        return [
            'store_name' => ['required', 'string', 'max:255'],
            'store_slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique(Vendor::class, 'store_slug')->ignore($vendorId),
            ],
            'business_email' => ['nullable', 'string', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+\-\s().]+$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'store_name' => is_string($this->store_name) ? trim($this->store_name) : $this->store_name,
            'store_slug' => is_string($this->store_slug) ? Str::slug($this->store_slug) : $this->store_slug,
            'business_email' => is_string($this->business_email) ? Str::lower(trim($this->business_email)) : $this->business_email,
            'business_phone' => is_string($this->business_phone) ? trim($this->business_phone) : $this->business_phone,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'address' => is_string($this->address) ? trim($this->address) : $this->address,
        ]);
    }
}
