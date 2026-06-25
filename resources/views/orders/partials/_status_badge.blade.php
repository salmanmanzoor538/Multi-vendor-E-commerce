@php
    $value = $status instanceof \App\Enums\OrderStatus ? $status->value : (string) $status;
    $classes = match ($value) {
        'pending' => 'bg-amber-100 text-amber-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'processing' => 'bg-indigo-100 text-indigo-800',
        'shipped' => 'bg-cyan-100 text-cyan-800',
        'delivered' => 'bg-emerald-100 text-emerald-800',
        'cancelled' => 'bg-rose-100 text-rose-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $classes }}">
    {{ $status instanceof \App\Enums\OrderStatus ? $status->label() : ucfirst((string) $status) }}
</span>
