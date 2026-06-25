<?php

namespace App\Http\Requests\Review;

use App\Models\Review;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VendorReplyReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $review = $this->route('review');

        return $this->user() !== null
            && $review instanceof Review
            && $this->user()->can('reply', $review);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vendor_reply' => ['required', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'vendor_reply' => is_string($this->vendor_reply) ? trim($this->vendor_reply) : $this->vendor_reply,
        ]);
    }
}
