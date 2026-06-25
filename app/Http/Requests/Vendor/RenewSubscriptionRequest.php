<?php

namespace App\Http\Requests\Vendor;

use App\Enums\BillingCycle;
use App\Models\Subscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RenewSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->can('renew', Subscription::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
