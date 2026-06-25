@extends('layouts.master', ['title' => 'Vendors'])

@section('content')
    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-semibold">Vendor Applications</h1>

            <form method="GET" action="{{ route('admin.vendors.index') }}" class="mt-4 flex items-end gap-3">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="mt-1 rounded-md border-gray-300 shadow-sm">
                        <option value="">All</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($activeStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Store</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Owner</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Submitted</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($vendors as $vendor)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $vendor->store_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $vendor->owner->name }} <span class="text-gray-500">({{ $vendor->owner->email }})</span></td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $statusLabels[$vendor->status->value] ?? ucfirst($vendor->status->value) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $vendor->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('admin.vendors.show', $vendor) }}" class="text-indigo-600 hover:text-indigo-900">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No vendor applications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $vendors->links() }}
        </div>
    </div>
@endsection
