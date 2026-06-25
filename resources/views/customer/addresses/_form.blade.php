@php
    $address = $address ?? null;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="type">Address Type</label>
        <select id="type" name="type" class="w-full rounded-md border-gray-300 text-sm" required>
            @foreach (['shipping' => 'Shipping', 'billing' => 'Billing'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $address?->type?->value) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="full_name">Full Name</label>
        <input id="full_name" type="text" name="full_name" value="{{ old('full_name', $address?->full_name) }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="phone">Phone</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $address?->phone) }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="country_code">Country Code</label>
        <input id="country_code" type="text" name="country_code" value="{{ old('country_code', $address?->country_code) }}" maxlength="2" class="w-full rounded-md border-gray-300 text-sm uppercase" required>
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700" for="line_1">Address Line 1</label>
        <input id="line_1" type="text" name="line_1" value="{{ old('line_1', $address?->line_1) }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-gray-700" for="line_2">Address Line 2</label>
        <input id="line_2" type="text" name="line_2" value="{{ old('line_2', $address?->line_2) }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="city">City</label>
        <input id="city" type="text" name="city" value="{{ old('city', $address?->city) }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="state">State</label>
        <input id="state" type="text" name="state" value="{{ old('state', $address?->state) }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-700" for="postal_code">Postal Code</label>
        <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $address?->postal_code) }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div class="flex items-center gap-2 pt-6">
        <input id="is_default" type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-indigo-600" @checked(old('is_default', $address?->is_default))>
        <label class="text-sm text-gray-700" for="is_default">Set as default for this type</label>
    </div>
</div>
