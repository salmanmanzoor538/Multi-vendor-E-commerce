<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Vendor;

class AdminVendorApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $vendor = $this->route('vendor');

        if (! $this->user() || ! $vendor instanceof Vendor) {
            return false;
        }

        if ($this->routeIs('admin.vendors.approve')) {
            return $this->user()->can('approve', $vendor);
        }

        if ($this->routeIs('admin.vendors.reject')) {
            return $this->user()->can('reject', $vendor);
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => [
                Rule::requiredIf($this->routeIs('admin.vendors.reject')),
                'string',
                'max:2000',
            ],
        ];
    }
}
