@php
    $isEdit = isset($attribute);
    $oldValues = old('values', $isEdit ? $attribute->values->map(fn ($value) => [
        'id' => $value->id,
        'value' => $value->value,
        'label' => $value->label,
        'code' => $value->code,
        'sort_order' => $value->sort_order,
        'is_active' => $value->is_active,
    ])->values()->all() : [
        ['value' => '', 'label' => '', 'code' => '', 'sort_order' => 0, 'is_active' => true],
    ]);
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label class="block text-sm font-medium text-gray-700" for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $attribute->name ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="slug">Slug</label>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $attribute->slug ?? '') }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="type">Type</label>
        <input id="type" name="type" type="text" value="{{ old('type', $attribute->type ?? 'text') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700" for="sort_order">Sort Order</label>
        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $attribute->sort_order ?? 0) }}" class="mt-1 w-full rounded-md border-gray-300">
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $attribute->is_active ?? true))>
            Active
        </label>
    </div>

    <div class="md:col-span-2">
        <div class="mb-2 flex items-center justify-between">
            <label class="block text-sm font-medium text-gray-700">Values</label>
            <button type="button" id="add-attribute-value" class="rounded-md border border-gray-300 px-3 py-1 text-sm text-gray-700 hover:bg-gray-100">Add Value</button>
        </div>
        <div id="attribute-values-wrapper" class="space-y-3"></div>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-black">
        {{ $isEdit ? 'Update Attribute' : 'Create Attribute' }}
    </button>
    <a href="{{ route('vendor.attributes.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
</div>

<script>
    (function () {
        const wrapper = document.getElementById('attribute-values-wrapper');
        const addButton = document.getElementById('add-attribute-value');
        const initialValues = @json($oldValues);

        const createInput = (name, type = 'text') => {
            const input = document.createElement('input');
            input.name = name;
            input.type = type;
            return input;
        };

        const createLabeledField = ({ label, input }) => {
            const container = document.createElement('div');
            const title = document.createElement('label');
            title.className = 'block text-xs font-medium text-gray-600';
            title.textContent = label;

            input.className = 'mt-1 w-full rounded-md border-gray-300 text-sm';
            container.appendChild(title);
            container.appendChild(input);

            return container;
        };

        const renderRow = (index, value = {}) => {
            const row = document.createElement('div');
            row.className = 'rounded-md border border-gray-200 bg-gray-50 p-3';
            const grid = document.createElement('div');
            grid.className = 'grid gap-3 md:grid-cols-5';
            row.appendChild(grid);

            const idInput = createInput(`values[${index}][id]`, 'hidden');
            idInput.value = value.id ?? '';
            grid.appendChild(idInput);

            const valueInput = createInput(`values[${index}][value]`);
            valueInput.required = true;
            valueInput.value = value.value ?? '';
            grid.appendChild(createLabeledField({ label: 'Value', input: valueInput }));

            const labelInput = createInput(`values[${index}][label]`);
            labelInput.value = value.label ?? '';
            grid.appendChild(createLabeledField({ label: 'Label', input: labelInput }));

            const codeInput = createInput(`values[${index}][code]`);
            codeInput.value = value.code ?? '';
            grid.appendChild(createLabeledField({ label: 'Code', input: codeInput }));

            const sortInput = createInput(`values[${index}][sort_order]`, 'number');
            sortInput.min = '0';
            sortInput.value = value.sort_order ?? 0;
            grid.appendChild(createLabeledField({ label: 'Sort', input: sortInput }));

            const actionCell = document.createElement('div');
            actionCell.className = 'flex items-end justify-between';

            const activeLabel = document.createElement('label');
            activeLabel.className = 'inline-flex items-center gap-2 text-xs text-gray-700';
            const isActiveInput = createInput(`values[${index}][is_active]`, 'checkbox');
            isActiveInput.value = '1';
            isActiveInput.checked = value.is_active ?? true;
            activeLabel.appendChild(isActiveInput);
            activeLabel.appendChild(document.createTextNode('Active'));

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'remove-attribute-value text-xs text-red-600 hover:text-red-800';
            removeButton.textContent = 'Remove';

            actionCell.appendChild(activeLabel);
            actionCell.appendChild(removeButton);
            grid.appendChild(actionCell);

            removeButton.addEventListener('click', () => {
                row.remove();
                normalizeIndexes();
            });

            return row;
        };

        const normalizeIndexes = () => {
            const rows = [...wrapper.children];
            wrapper.innerHTML = '';
            rows.forEach((row, index) => {
                const values = {};
                row.querySelectorAll('input').forEach((input) => {
                    const fieldMatch = input.name.match(/\[(id|value|label|code|sort_order|is_active)\]$/);
                    if (!fieldMatch) {
                        return;
                    }
                    const key = fieldMatch[1];
                    values[key] = input.type === 'checkbox' ? input.checked : input.value;
                });
                wrapper.appendChild(renderRow(index, values));
            });
        };

        initialValues.forEach((value, index) => wrapper.appendChild(renderRow(index, value)));

        addButton.addEventListener('click', () => {
            wrapper.appendChild(renderRow(wrapper.children.length, {}));
        });
    })();
</script>
