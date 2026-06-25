<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAnalyticsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::ADMIN) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxLimit = (int) config('analytics.dashboard.max_vendor_leaderboard_limit', 50);

        return [
            'range' => [
                'nullable',
                Rule::in(array_merge(array_keys(config('analytics.dashboard.ranges', [])), ['custom'])),
            ],
            'from' => [
                Rule::requiredIf((string) $this->input('range') === 'custom'),
                'nullable',
                'date',
            ],
            'to' => [
                Rule::requiredIf((string) $this->input('range') === 'custom'),
                'nullable',
                'date',
                'after_or_equal:from',
            ],
            'limit' => ['nullable', 'integer', 'min:1', 'max:'.$maxLimit],
        ];
    }
}
