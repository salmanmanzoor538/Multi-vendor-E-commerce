@extends('layouts.master', ['title' => 'Attributes'])

@section('content')
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Attributes</h1>
                <p class="mt-1 text-sm text-gray-600">Manage vendor-specific options like size, color, and material.</p>
            </div>
            <a href="{{ route('vendor.attributes.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">Add Attribute</a>
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Values</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($attributes as $attribute)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $attribute->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $attribute->type }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $attribute->values->pluck('label')->implode(', ') }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $attribute->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $attribute->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-3 text-sm">
                                    <a href="{{ route('vendor.attributes.edit', $attribute) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                    <form method="POST" action="{{ route('vendor.attributes.destroy', $attribute) }}" onsubmit="return confirm('Delete this attribute?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No attributes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $attributes->links() }}
        </div>
    </div>
@endsection
