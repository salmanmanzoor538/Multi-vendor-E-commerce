<?php

namespace App\Http\Requests\Review;

use App\Enums\ReviewStatus;
use App\Models\Review;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModerateReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $review = $this->route('review');

        return $this->user() !== null
            && $review instanceof Review
            && $this->user()->can('moderate', $review);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                ReviewStatus::APPROVED->value,
                ReviewStatus::REJECTED->value,
            ])],
            'moderation_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'moderation_note' => is_string($this->moderation_note) ? trim($this->moderation_note) : $this->moderation_note,
        ]);
    }
}
