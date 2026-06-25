import { escapeHtml } from '../utils/safe-dom';

const $ = window.jQuery || window.$;

if (!$) {
    // The module is loaded globally; if jQuery is unavailable on a page layout, skip boot.
    // eslint-disable-next-line no-console
    console.warn('jQuery is not loaded. Variant generator is disabled on this page.');
}

const parseJson = (value, fallback = []) => {
    try {
        return JSON.parse(value || '');
    } catch {
        return fallback;
    }
};

const asNumber = (value, fallback = 0) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
};

const snapshotLabel = (snapshot = []) => snapshot
    .map((item) => item.attribute_value_label || item.attribute_value || '')
    .filter(Boolean)
    .join(' / ');

const readCurrentRows = ($rowsContainer) => {
    const rows = [];
    $rowsContainer.find('tr').each((_, row) => {
        const $row = $(row);
        rows.push({
            combination_key: $row.data('combination-key'),
            sku: $row.find('[data-field="sku"]').val() || '',
            price: $row.find('[data-field="price"]').val() || '0.00',
            stock_qty: asNumber($row.find('[data-field="stock_qty"]').val(), 0),
            status: $row.find('[data-field="status"]').val() || 'active',
            option_snapshot: parseJson($row.find('[data-field="snapshot_json"]').val(), []),
            values: parseJson($row.find('[data-field="values_json"]').val(), []),
        });
    });
    return rows;
};

const renderRows = ($rowsContainer, variants) => {
    $rowsContainer.empty();

    variants.forEach((variant, index) => {
        const label = snapshotLabel(variant.option_snapshot);
        const row = `
            <tr data-combination-key="${escapeHtml(variant.combination_key)}">
                <td class="px-3 py-3 text-sm text-gray-800">
                    ${escapeHtml(label || variant.combination_key)}
                    <input type="hidden" name="variants[${index}][combination_key]" value="${escapeHtml(variant.combination_key)}">
                    <input type="hidden" data-field="snapshot_json" name="variants[${index}][option_snapshot_json]" value='${escapeHtml(JSON.stringify(variant.option_snapshot))}'>
                    <input type="hidden" data-field="values_json" name="variants[${index}][values_json]" value='${escapeHtml(JSON.stringify(variant.values))}'>
                    ${variant.option_snapshot.map((item, i) => `
                        <input type="hidden" name="variants[${index}][option_snapshot][${i}][attribute_id]" value="${escapeHtml(item.attribute_id)}">
                        <input type="hidden" name="variants[${index}][option_snapshot][${i}][attribute_name]" value="${escapeHtml(item.attribute_name || '')}">
                        <input type="hidden" name="variants[${index}][option_snapshot][${i}][attribute_slug]" value="${escapeHtml(item.attribute_slug || '')}">
                        <input type="hidden" name="variants[${index}][option_snapshot][${i}][attribute_value_id]" value="${escapeHtml(item.attribute_value_id)}">
                        <input type="hidden" name="variants[${index}][option_snapshot][${i}][attribute_value]" value="${escapeHtml(item.attribute_value || '')}">
                        <input type="hidden" name="variants[${index}][option_snapshot][${i}][attribute_value_label]" value="${escapeHtml(item.attribute_value_label || '')}">
                        <input type="hidden" name="variants[${index}][option_snapshot][${i}][attribute_value_code]" value="${escapeHtml(item.attribute_value_code || '')}">
                    `).join('')}
                    ${variant.values.map((item, i) => `
                        <input type="hidden" name="variants[${index}][values][${i}][attribute_id]" value="${escapeHtml(item.attribute_id)}">
                        <input type="hidden" name="variants[${index}][values][${i}][attribute_value_id]" value="${escapeHtml(item.attribute_value_id)}">
                    `).join('')}
                </td>
                <td class="px-3 py-3">
                    <input data-field="sku" name="variants[${index}][sku]" type="text" value="${escapeHtml(variant.sku || '')}" class="w-44 rounded-md border-gray-300 text-sm">
                </td>
                <td class="px-3 py-3">
                    <input data-field="price" name="variants[${index}][price]" type="number" step="0.01" min="0" value="${escapeHtml(variant.price || '0.00')}" class="w-28 rounded-md border-gray-300 text-sm">
                </td>
                <td class="px-3 py-3">
                    <input data-field="stock_qty" name="variants[${index}][stock_qty]" type="number" min="0" value="${escapeHtml(variant.stock_qty ?? 0)}" class="w-24 rounded-md border-gray-300 text-sm">
                </td>
                <td class="px-3 py-3">
                    <select data-field="status" name="variants[${index}][status]" class="w-28 rounded-md border-gray-300 text-sm">
                        <option value="active" ${variant.status === 'active' ? 'selected' : ''}>Active</option>
                        <option value="inactive" ${variant.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                    </select>
                </td>
            </tr>
        `;
        $rowsContainer.append(row);
    });
};

const buildAttributeCards = ($list, attributes) => {
    $list.empty();

    attributes.forEach((attribute) => {
        const optionsHtml = attribute.values.map((value) => `
            <option value="${escapeHtml(value.id)}">
                ${escapeHtml(value.label || value.value)}
            </option>
        `).join('');

        const card = `
            <div class="rounded-md border border-gray-200 p-3">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-800">
                    <input type="checkbox" class="js-attribute-toggle" data-attribute-id="${escapeHtml(attribute.id)}">
                    ${escapeHtml(attribute.name)}
                </label>
                <select multiple class="js-value-select mt-2 h-28 w-full rounded-md border-gray-300 text-sm" data-attribute-id="${escapeHtml(attribute.id)}" disabled>
                    ${optionsHtml}
                </select>
            </div>
        `;

        $list.append(card);
    });
};

const bootVariantGenerator = (container) => {
    const $container = $(container);
    const $attributeList = $container.find('.js-attribute-list');
    const $rows = $container.find('.js-variant-rows');
    const $message = $container.find('.js-generate-message');

    const attributes = parseJson($container.attr('data-attributes'), []);
    const existingVariants = parseJson($container.attr('data-existing-variants'), []);
    const generateUrl = $container.attr('data-generate-url');
    buildAttributeCards($attributeList, attributes);

    $attributeList.on('change', '.js-attribute-toggle', function onToggle() {
        const attributeId = $(this).data('attribute-id');
        const enabled = $(this).is(':checked');
        $attributeList.find(`.js-value-select[data-attribute-id="${attributeId}"]`)
            .prop('disabled', !enabled);
    });

    if (existingVariants.length > 0) {
        renderRows($rows, existingVariants);
    }

    $container.find('.js-generate-variants').on('click', () => {
        const selections = [];
        $attributeList.find('.js-attribute-toggle:checked').each((_, checkbox) => {
            const attributeId = $(checkbox).data('attribute-id');
            const selectedValueIds = $attributeList
                .find(`.js-value-select[data-attribute-id="${attributeId}"]`)
                .val() || [];

            if (selectedValueIds.length > 0) {
                selections.push({
                    attribute_id: Number(attributeId),
                    value_ids: selectedValueIds.map((id) => Number(id)),
                });
            }
        });

        if (selections.length === 0) {
            $message.text('Select at least one attribute with values.');
            return;
        }

        const baseSkuSelector = $container.attr('data-base-sku-selector');
        const baseSku = $(baseSkuSelector).val() || '';

        $.ajax({
            url: generateUrl,
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            data: {
                base_sku: baseSku,
                attributes: selections,
            },
        }).done((response) => {
            const generated = response.variants || [];
            const currentByKey = Object.fromEntries(
                readCurrentRows($rows).map((row) => [row.combination_key, row]),
            );

            const merged = generated.map((row) => {
                const existing = currentByKey[row.combination_key];
                const values = row.option_snapshot.map((item) => ({
                    attribute_id: item.attribute_id,
                    attribute_value_id: item.attribute_value_id,
                }));

                return {
                    combination_key: row.combination_key,
                    sku: existing?.sku || row.generated_sku || '',
                    price: existing?.price || '0.00',
                    stock_qty: existing?.stock_qty ?? 0,
                    status: existing?.status || row.status || 'active',
                    option_snapshot: row.option_snapshot || [],
                    values,
                };
            });

            renderRows($rows, merged);
            $message.text(`${merged.length} variants generated.`);
        }).fail((xhr) => {
            const fallback = 'Failed to generate variants.';
            const validationErrors = xhr?.responseJSON?.errors;
            if (validationErrors) {
                const firstError = Object.values(validationErrors)[0];
                $message.text(Array.isArray(firstError) ? firstError[0] : fallback);
                return;
            }
            $message.text(fallback);
        });
    });
};

if ($) {
    $(function initVariantGenerators() {
        $('.js-variant-generator').each((_, element) => bootVariantGenerator(element));
    });
}
