<?php

namespace App\Http\Requests\Vendor;

use App\Models\Attribute;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $attribute = $this->route('attribute');

        return $this->user() !== null
            && $attribute instanceof Attribute
            && $this->user()->can('update', $attribute);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $attribute = $this->route('attribute');
        $vendorId = (int) ($this->user()?->ownedVendor?->id ?? $this->user()?->vendor_id);

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique(Attribute::class, 'slug')
                    ->ignore($attribute?->id)
                    ->where(fn ($query) => $query->where('vendor_id', $vendorId)),
            ],
            'type' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'values' => ['required', 'array', 'min:1'],
            'values.*.id' => ['nullable', 'integer'],
            'values.*.value' => ['required', 'string', 'max:255'],
            'values.*.label' => ['nullable', 'string', 'max:255'],
            'values.*.code' => ['nullable', 'string', 'max:60'],
            'values.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'values.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $values = collect((array) $this->input('values', []))
            ->map(function ($value): array {
                $row = is_array($value) ? $value : [];

                return [
                    'id' => $row['id'] ?? null,
                    'value' => isset($row['value']) && is_string($row['value']) ? trim($row['value']) : ($row['value'] ?? null),
                    'label' => isset($row['label']) && is_string($row['label']) ? trim($row['label']) : ($row['label'] ?? null),
                    'code' => isset($row['code']) && is_string($row['code']) ? trim($row['code']) : ($row['code'] ?? null),
                    'sort_order' => $row['sort_order'] ?? null,
                    'is_active' => filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->all();

        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'slug' => is_string($this->slug) ? Str::slug($this->slug) : $this->slug,
            'type' => is_string($this->type) ? trim(Str::lower($this->type)) : $this->type,
            'is_active' => $this->boolean('is_active'),
            'values' => $values,
        ]);
    }
}
