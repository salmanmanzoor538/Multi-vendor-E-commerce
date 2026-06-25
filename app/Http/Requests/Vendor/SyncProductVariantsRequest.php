<?php

namespace App\Http\Requests\Vendor;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncProductVariantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $this->user() !== null
            && $product instanceof Product
            && $this->user()->can('sync', [ProductVariant::class, $product]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vendorId = (int) ($this->user()?->ownedVendor?->id ?? $this->user()?->vendor_id);

        return [
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.combination_key' => ['required', 'string', 'max:500'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['required', 'numeric', 'min:0'],
            'variants.*.stock_qty' => ['required', 'integer', 'min:0'],
            'variants.*.status' => ['required', Rule::in(config('product_variant.statuses', ['active', 'inactive']))],
            'variants.*.option_snapshot' => ['nullable', 'array'],
            'variants.*.option_snapshot.*.attribute_id' => ['required', 'integer'],
            'variants.*.option_snapshot.*.attribute_name' => ['nullable', 'string'],
            'variants.*.option_snapshot.*.attribute_slug' => ['nullable', 'string'],
            'variants.*.option_snapshot.*.attribute_value_id' => ['required', 'integer'],
            'variants.*.option_snapshot.*.attribute_value' => ['nullable', 'string'],
            'variants.*.option_snapshot.*.attribute_value_label' => ['nullable', 'string'],
            'variants.*.option_snapshot.*.attribute_value_code' => ['nullable', 'string'],
            'variants.*.values' => ['required', 'array', 'min:1'],
            'variants.*.values.*.attribute_id' => [
                'required',
                'integer',
                Rule::exists(Attribute::class, 'id')->where(fn ($query) => $query->where('vendor_id', $vendorId)),
            ],
            'variants.*.values.*.attribute_value_id' => [
                'required',
                'integer',
                Rule::exists(AttributeValue::class, 'id')->where(fn ($query) => $query->where('vendor_id', $vendorId)),
            ],
        ];
    }
}
