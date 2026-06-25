<?php

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', Plan::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(Plan::class, 'slug')],
            'description' => ['nullable', 'string', 'max:5000'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'limits' => ['required', 'array'],
            'limits.product_count' => ['nullable', 'integer', 'min:1'],
            'limits.storage_mb' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'slug' => is_string($this->slug) ? Str::slug($this->slug) : $this->slug,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
