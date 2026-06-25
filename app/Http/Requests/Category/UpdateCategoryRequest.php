<?php

namespace App\Http\Requests\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $category = $this->route('category');

        return $this->user() !== null
            && $category !== null
            && $this->user()->can('update', $category);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(Category::class, 'slug')->ignore($categoryId)],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $category = $this->route('category');
            $parentId = $this->input('parent_id');

            if (! $category || ! $parentId) {
                return;
            }

            if ((int) $parentId === (int) $category->id) {
                $validator->errors()->add('parent_id', 'A category cannot be its own parent.');

                return;
            }

            if ($this->isDescendant((int) $category->id, (int) $parentId)) {
                $validator->errors()->add('parent_id', 'A category cannot be moved under its own child.');
            }
        });
    }

    private function isDescendant(int $categoryId, int $parentId): bool
    {
        $currentParentId = $parentId;

        while ($currentParentId !== null) {
            if ($currentParentId === $categoryId) {
                return true;
            }

            $currentParentId = Category::query()->whereKey($currentParentId)->value('parent_id');
        }

        return false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'slug' => is_string($this->slug) ? Str::slug($this->slug) : $this->slug,
        ]);
    }
}
