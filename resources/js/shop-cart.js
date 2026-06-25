const $ = window.jQuery || window.$;

if (!$) {
    // eslint-disable-next-line no-console
    console.warn('jQuery is not loaded. Cart interactions are disabled.');
}

const request = ({ url, method, data = {} }) => $.ajax({
    url,
    method,
    headers: {
        Accept: 'application/json',
    },
    data,
});

const showMessage = ($node, message, type = 'success') => {
    if (!$node.length) return;

    const isError = type === 'error';
    $node
        .removeClass('hidden border-green-200 bg-green-50 text-green-800 border-red-200 bg-red-50 text-red-800')
        .addClass(isError ? 'border-red-200 bg-red-50 text-red-800' : 'border-green-200 bg-green-50 text-green-800')
        .text(message || '');
};

const createLineItemNode = (item, minQty, maxQty) => {
    const $row = $('<div>', {
        class: 'flex flex-col gap-3 rounded-md border border-gray-100 p-4 md:flex-row md:items-center md:justify-between',
        'data-line-key': item.line_key,
    });

    const $details = $('<div>');
    $details.append($('<p>', { class: 'text-sm font-semibold text-gray-900', text: item.name_snapshot }));
    $details.append($('<p>', { class: 'text-xs text-gray-500', text: `SKU: ${item.sku_snapshot}` }));

    const $controls = $('<div>', { class: 'flex items-center gap-3' });
    $controls.append($('<input>', {
        type: 'number',
        class: 'w-20 rounded-md border-gray-300 text-sm',
        min: minQty,
        max: maxQty,
        value: item.quantity,
        'data-item-quantity': '',
    }));
    $controls.append($('<button>', {
        type: 'button',
        class: 'rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700',
        text: 'Update',
        'data-update-item': '',
    }));
    $controls.append($('<button>', {
        type: 'button',
        class: 'rounded-md border border-red-300 px-3 py-2 text-xs font-medium text-red-600 hover:bg-red-50',
        text: 'Remove',
        'data-remove-item': '',
    }));

    const $totals = $('<div>', { class: 'text-right' });
    $totals.append($('<p>', { class: 'text-xs text-gray-500', text: `Unit: ${item.unit_price}` }));
    $totals.append($('<p>', { class: 'text-sm font-semibold text-gray-900', text: `Line: ${item.line_total}` }));

    $row.append($details, $controls, $totals);

    return $row;
};

const createGroupNode = (group, minQty, maxQty) => {
    const $section = $('<section>', {
        class: 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm',
        'data-vendor-group': group.vendor_id,
    });

    const $header = $('<div>', { class: 'mb-4 flex items-center justify-between border-b border-gray-100 pb-3' });
    $header.append($('<h2>', { class: 'text-lg font-semibold', text: group.vendor_name }));

    const $subtotal = $('<p>', { class: 'text-sm text-gray-600' });
    $subtotal.text('Subtotal: ');
    $subtotal.append($('<span>', { class: 'font-semibold', text: group.subtotal }));

    $header.append($subtotal);

    const $items = $('<div>', { class: 'space-y-3' });
    (group.items || []).forEach((item) => {
        $items.append(createLineItemNode(item, minQty, maxQty));
    });

    $section.append($header, $items);

    return $section;
};

const renderCart = ($root, cart) => {
    const groups = cart?.groups || [];
    const totals = cart?.totals || { items_count: 0, grand_total: '0.00' };

    const $groups = $root.find('[data-cart-groups]');
    const $totals = $root.find('[data-cart-totals]');

    const minQty = Number($root.data('minQty') || 1);
    const maxQty = Number($root.data('maxQty') || 99);

    $groups.empty();

    if (groups.length === 0) {
        $groups.append($('<div>', {
            class: 'rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-600',
            'data-empty-state': '',
            text: 'Your cart is empty.',
        }));
    } else {
        groups.forEach((group) => {
            $groups.append(createGroupNode(group, minQty, maxQty));
        });
    }

    $totals.find('[data-items-count]').text(totals.items_count);
    $totals.find('[data-grand-total]').text(totals.grand_total);
};

const bootShopAddToCart = () => {
    const $config = $('#shop-cart-config');
    if (!$config.length) return;

    const addUrl = $config.data('addUrl');
    const $feedback = $('[data-cart-feedback]');

    $(document).on('submit', '[data-cart-add-form]', function onSubmit(e) {
        e.preventDefault();
        const $form = $(this);

        request({
            url: addUrl,
            method: 'POST',
            data: $form.serialize(),
        }).done((response) => {
            showMessage($feedback, response.message || 'Item added to cart.');
        }).fail((xhr) => {
            const firstError = Object.values(xhr?.responseJSON?.errors || {})[0];
            const message = Array.isArray(firstError) ? firstError[0] : 'Unable to add item to cart.';
            showMessage($feedback, message, 'error');
        });
    });
};

const bootCartPage = () => {
    const $root = $('#cart-root');
    if (!$root.length) return;

    const updateTemplate = String($root.data('updateUrlTemplate') || '');
    const removeTemplate = String($root.data('removeUrlTemplate') || '');
    const $error = $('[data-cart-error]');

    const resolveUrl = (template, lineKey) => template.replace('__LINE_KEY__', encodeURIComponent(lineKey));

    const updateQuantity = ($row) => {
        const lineKey = String($row.data('lineKey'));
        const quantity = Number($row.find('[data-item-quantity]').val() || 1);

        return request({
            url: resolveUrl(updateTemplate, lineKey),
            method: 'PATCH',
            data: { quantity },
        });
    };

    const removeItem = ($row) => {
        const lineKey = String($row.data('lineKey'));

        return request({
            url: resolveUrl(removeTemplate, lineKey),
            method: 'DELETE',
        });
    };

    $(document).on('click', '[data-update-item]', function onUpdate() {
        const $row = $(this).closest('[data-line-key]');

        updateQuantity($row).done((response) => {
            renderCart($root, response.cart || {});
            $error.addClass('hidden').text('');
        }).fail((xhr) => {
            const firstError = Object.values(xhr?.responseJSON?.errors || {})[0];
            const message = Array.isArray(firstError) ? firstError[0] : 'Unable to update cart item.';
            showMessage($error, message, 'error');
        });
    });

    $(document).on('click', '[data-remove-item]', function onRemove() {
        const $row = $(this).closest('[data-line-key]');

        removeItem($row).done((response) => {
            renderCart($root, response.cart || {});
            $error.addClass('hidden').text('');
        }).fail(() => {
            showMessage($error, 'Unable to remove cart item.', 'error');
        });
    });
};

if ($) {
    $(function initCart() {
        bootShopAddToCart();
        bootCartPage();
    });
}
