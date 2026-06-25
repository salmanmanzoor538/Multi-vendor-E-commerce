<?php

namespace App\Http\Requests\Vendor;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GenerateProductVariantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('generate', ProductVariant::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vendorId = (int) ($this->user()?->ownedVendor?->id ?? $this->user()?->vendor_id);

        return [
            'base_sku' => ['nullable', 'string', 'max:255'],
            'attributes' => ['required', 'array', 'min:1'],
            'attributes.*.attribute_id' => [
                'required',
                'integer',
                Rule::exists(Attribute::class, 'id')->where(fn ($query) => $query->where('vendor_id', $vendorId)),
            ],
            'attributes.*.value_ids' => ['required', 'array', 'min:1'],
            'attributes.*.value_ids.*' => [
                'required',
                'integer',
                Rule::exists(AttributeValue::class, 'id')->where(fn ($query) => $query->where('vendor_id', $vendorId)),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'base_sku' => is_string($this->base_sku) ? Str::upper(trim($this->base_sku)) : $this->base_sku,
        ]);
    }
}
