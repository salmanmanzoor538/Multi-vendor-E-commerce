<div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
            <tr>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Slug</th>
                <th class="px-4 py-3">Created By</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white text-gray-700">
            @php
                $renderRows = function ($nodes, $depth = 0) use (&$renderRows, $routePrefix) {
                    foreach ($nodes as $node) {
                        echo '<tr>';
                        echo '<td class="px-4 py-3 font-medium text-gray-900">'.e(str_repeat('-- ', $depth).$node->name).'</td>';
                        echo '<td class="px-4 py-3">'.e($node->slug).'</td>';
                        echo '<td class="px-4 py-3">'.e($node->creator?->name ?? 'System').'</td>';
                        echo '<td class="px-4 py-3">';
                        echo '<div class="flex items-center gap-3">';
                        if (auth()->user()->can('update', $node)) {
                            echo '<a href="'.e(route($routePrefix.'.categories.edit', $node)).'" class="text-indigo-600 hover:text-indigo-800">Edit</a>';
                        }
                        if (auth()->user()->can('delete', $node)) {
                            echo '<form method="POST" action="'.e(route($routePrefix.'.categories.destroy', $node)).'" onsubmit="return confirm(\'Delete this category?\');">';
                            echo csrf_field();
                            echo method_field('DELETE');
                            echo '<button type="submit" class="text-red-600 hover:text-red-800">Delete</button>';
                            echo '</form>';
                        }
                        echo '</div>';
                        echo '</td>';
                        echo '</tr>';

                        if ($node->childrenRecursive->isNotEmpty()) {
                            $renderRows($node->childrenRecursive, $depth + 1);
                        }
                    }
                };
            @endphp

            @if ($categories->isEmpty())
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No categories found.</td>
                </tr>
            @else
                @php $renderRows($categories); @endphp
            @endif
        </tbody>
    </table>
</div>

