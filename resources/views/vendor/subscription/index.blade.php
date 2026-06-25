@extends('layouts.master', ['title' => 'Subscription'])

@section('content')
    @include('partials._alerts')

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold">Vendor Subscription</h1>
            @if ($activeSubscription)
                <p class="mt-2 text-sm text-gray-600">
                    Active plan: <strong>{{ $activeSubscription->plan?->name }}</strong>
                    ({{ ucfirst($activeSubscription->billing_cycle->value) }})
                    until {{ $activeSubscription->ends_at?->format('M d, Y H:i') }}.
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <form action="{{ route('vendor.subscription.renew') }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <select name="billing_cycle" class="rounded-md border-gray-300 text-sm">
                            @foreach ($billingCycles as $cycle)
                                <option value="{{ $cycle->value }}">{{ ucfirst($cycle->value) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">Renew</button>
                    </form>

                    <form action="{{ route('vendor.subscription.cancel') }}" method="POST" onsubmit="return confirm('Cancel active subscription now?');">
                        @csrf
                        <button type="submit" class="rounded-md border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Cancel Subscription</button>
                    </form>
                </div>
            @else
                <p class="mt-2 text-sm text-gray-600">No active subscription found. Select a plan below to continue.</p>
            @endif
            @error('subscription')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($plans as $plan)
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">{{ $plan->name }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $plan->description ?: 'No description available.' }}</p>

                    <div class="mt-3 space-y-1 text-sm text-gray-700">
                        <p>Monthly: {{ number_format((float) $plan->monthly_price, 2) }}</p>
                        <p>Yearly: {{ number_format((float) $plan->yearly_price, 2) }}</p>
                        <p>Products: {{ data_get($plan->limits, 'product_count') ?? 'Unlimited' }}</p>
                        <p>Storage: {{ data_get($plan->limits, 'storage_mb') ? data_get($plan->limits, 'storage_mb').' MB' : 'Unlimited' }}</p>
                    </div>

                    <form action="{{ route('vendor.subscription.store') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Billing Cycle</label>
                            <select name="billing_cycle" class="w-full rounded-md border-gray-300 text-sm">
                                @foreach ($billingCycles as $cycle)
                                    <option value="{{ $cycle->value }}" @selected(old('billing_cycle') === $cycle->value)>{{ ucfirst($cycle->value) }}</option>
                                @endforeach
                            </select>
                            @error('billing_cycle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            @error('plan_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">
                            {{ $activeSubscription ? 'Switch (after cancel)' : 'Subscribe' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endsection
