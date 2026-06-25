<?php

namespace App\Http\Requests\Vendor;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreVendorApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user === null || ! $user->hasRole(UserRole::ADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isGuest = $this->user() === null;

        return [
            'name' => [Rule::requiredIf($isGuest), 'string', 'max:255'],
            'email' => [Rule::requiredIf($isGuest), 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => [Rule::requiredIf($isGuest), 'confirmed', Password::defaults()],
            'store_name' => ['required', 'string', 'max:255'],
            'store_slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(Vendor::class, 'store_slug')],
            'business_email' => ['nullable', 'string', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+\-\s().]+$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'email' => is_string($this->email) ? Str::lower(trim($this->email)) : $this->email,
            'store_name' => is_string($this->store_name) ? trim($this->store_name) : $this->store_name,
            'store_slug' => is_string($this->store_slug) ? Str::slug($this->store_slug) : $this->store_slug,
            'business_email' => is_string($this->business_email) ? Str::lower(trim($this->business_email)) : $this->business_email,
            'business_phone' => is_string($this->business_phone) ? trim($this->business_phone) : $this->business_phone,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'address' => is_string($this->address) ? trim($this->address) : $this->address,
        ]);
    }
}
