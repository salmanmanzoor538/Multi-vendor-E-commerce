<?php

namespace App\Http\Requests\Vendor;

use App\Enums\BillingCycle;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('create', Subscription::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'plan_id' => [
                'required',
                'integer',
                Rule::exists(Plan::class, 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'billing_cycle' => ['required', Rule::in(array_column(BillingCycle::cases(), 'value'))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'billing_cycle' => is_string($this->billing_cycle) ? strtolower(trim($this->billing_cycle)) : $this->billing_cycle,
        ]);
    }
}
