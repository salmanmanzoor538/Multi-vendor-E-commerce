@extends('layouts.master', ['title' => 'Subscription Plans'])

@section('content')
    @include('partials._alerts')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Subscription Plans</h1>
            <p class="mt-1 text-sm text-gray-600">Manage vendor billing plans and usage limits.</p>
        </div>
        <a href="{{ route('admin.plans.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Add Plan</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Monthly</th>
                    <th class="px-4 py-3">Yearly</th>
                    <th class="px-4 py-3">Limits</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($plans as $plan)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $plan->name }}</p>
                            <p class="text-xs text-gray-500">{{ $plan->slug }}</p>
                        </td>
                        <td class="px-4 py-3">{{ number_format((float) $plan->monthly_price, 2) }}</td>
                        <td class="px-4 py-3">{{ number_format((float) $plan->yearly_price, 2) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            Products: {{ data_get($plan->limits, 'product_count') ?? 'Unlimited' }}<br>
                            Storage: {{ data_get($plan->limits, 'storage_mb') ? data_get($plan->limits, 'storage_mb').' MB' : 'Unlimited' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs {{ $plan->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $plan->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                @if ($plan->is_active)
                                    <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('Deactivate this plan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700">Deactivate</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No plans found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $plans->links() }}
    </div>
@endsection
