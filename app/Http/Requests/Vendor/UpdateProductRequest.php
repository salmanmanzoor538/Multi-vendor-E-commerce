<?php

namespace App\Http\Requests\Vendor;

use App\Enums\ProductStatus;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $this->user() !== null
            && $product instanceof Product
            && $this->user()->can('update', $product);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $vendorId = (int) ($this->user()?->ownedVendor?->id ?? $this->user()?->vendor_id);
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique(Product::class, 'sku')
                    ->ignore($productId)
                    ->where(fn ($query) => $query->where('vendor_id', $vendorId)),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_column(ProductStatus::cases(), 'value'))],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'image_alt_texts' => ['nullable', 'array'],
            'image_alt_texts.*' => ['nullable', 'string', 'max:255'],
            'primary_image' => ['nullable', 'string', 'regex:/^(existing:\d+|new:\d+)$/'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => [
                'integer',
                Rule::exists('product_images', 'id')->where(fn ($query) => $query->where('product_id', $productId)),
            ],
            'variants' => ['nullable', 'array'],
            'variants.*.combination_key' => ['required_with:variants', 'string', 'max:500'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock_qty' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.status' => ['required_with:variants', Rule::in(config('product_variant.statuses', ['active', 'inactive']))],
            'variants.*.option_snapshot' => ['nullable', 'array'],
            'variants.*.values' => ['required_with:variants', 'array', 'min:1'],
            'variants.*.values.*.attribute_id' => [
                'required_with:variants',
                'integer',
                Rule::exists(Attribute::class, 'id')->where(fn ($query) => $query->where('vendor_id', $vendorId)),
            ],
            'variants.*.values.*.attribute_value_id' => [
                'required_with:variants',
                'integer',
                Rule::exists(AttributeValue::class, 'id')->where(fn ($query) => $query->where('vendor_id', $vendorId)),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'sku' => is_string($this->sku) ? Str::upper(trim($this->sku)) : $this->sku,
            'image_alt_texts' => collect((array) $this->input('image_alt_texts', []))
                ->map(static fn ($value) => is_string($value) ? trim($value) : $value)
                ->all(),
        ]);
    }
}
