<?php

namespace App\Http\Requests\Review;

use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', Review::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $min = (int) config('review.rating.min', 1);
        $max = (int) config('review.rating.max', 5);

        return [
            'order_item_id' => ['required', Rule::exists(OrderItem::class, 'id')],
            'rating' => ['required', 'integer', 'min:'.$min, 'max:'.$max],
            'comment' => ['required', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'comment' => is_string($this->comment) ? trim($this->comment) : $this->comment,
        ]);
    }
}
