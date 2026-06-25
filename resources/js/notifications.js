import { escapeHtml } from './utils/safe-dom';

const $ = window.jQuery || window.$;

if (!$) {
    // eslint-disable-next-line no-console
    console.warn('jQuery is not loaded. Notification center is disabled.');
}

const request = ({ url, method, data = {} }) => $.ajax({
    url,
    method,
    data,
    headers: {
        Accept: 'application/json',
    },
});

const notificationItemHtml = (notification) => {
    const data = notification?.data || {};
    const title = data.title || 'Notification';
    const message = data.message || '';
    const url = data.url || '#';
    const createdAt = notification.created_at || '';
    const isRead = Boolean(notification.read_at);

    return `
        <li class="border-b border-gray-100 p-3 last:border-b-0 ${isRead ? 'opacity-75' : ''}" data-notification-item="${notification.id}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <a href="${escapeHtml(url)}" class="block truncate text-sm font-semibold text-gray-900 hover:text-indigo-600">
                        ${escapeHtml(title)}
                    </a>
                    <p class="mt-1 text-xs text-gray-600">${escapeHtml(message)}</p>
                    <p class="mt-1 text-[11px] text-gray-500">${escapeHtml(createdAt)}</p>
                </div>
                ${isRead ? '' : `
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-2 py-1 text-[11px] font-medium text-gray-700 hover:bg-gray-50"
                        data-mark-read
                        data-notification-id="${notification.id}"
                    >
                        Read
                    </button>
                `}
            </div>
        </li>
    `;
};

const bootNotificationCenter = () => {
    const $root = $('[data-notification-center]');
    if (!$root.length || !$) return;

    const feedUrl = String($root.data('feedUrl') || '');
    const markReadTemplate = String($root.data('markReadTemplate') || '');
    const markAllUrl = String($root.data('markAllUrl') || '');
    const pollIntervalMs = Number($root.data('pollIntervalMs') || 30000);
    const feedLimit = Number($root.data('feedLimit') || 10);

    if (!feedUrl || !markReadTemplate || !markAllUrl) return;

    const $toggle = $root.find('[data-notification-toggle]');
    const $panel = $root.find('[data-notification-panel]');
    const $badge = $root.find('[data-unread-badge]');
    const $list = $root.find('[data-notification-list]');
    const $markAll = $root.find('[data-mark-all-read]');
    const $empty = $root.find('[data-notification-empty]');

    const resolveReadUrl = (id) => markReadTemplate.replace('__NOTIFICATION_ID__', encodeURIComponent(id));

    const setUnreadCount = (count) => {
        const unreadCount = Number(count || 0);
        if (unreadCount > 0) {
            $badge.text(unreadCount).removeClass('hidden');
            $markAll.prop('disabled', false);
            return;
        }

        $badge.addClass('hidden').text('0');
        $markAll.prop('disabled', true);
    };

    const render = (payload) => {
        const notifications = payload?.notifications || [];
        setUnreadCount(payload?.unread_count || 0);

        if (!notifications.length) {
            $list.empty();
            $empty.removeClass('hidden');
            return;
        }

        $empty.addClass('hidden');
        $list.html(notifications.map(notificationItemHtml).join(''));
    };

    const fetchFeed = () => request({
        url: feedUrl,
        method: 'GET',
        data: { limit: feedLimit },
    }).done((response) => {
        render(response);
    });

    $toggle.on('click', (event) => {
        event.preventDefault();
        $panel.toggleClass('hidden');
        if (!$panel.hasClass('hidden')) {
            fetchFeed();
        }
    });

    $(document).on('click', (event) => {
        const clickedInside = $(event.target).closest('[data-notification-center]').length > 0;
        if (!clickedInside) {
            $panel.addClass('hidden');
        }
    });

    $root.on('click', '[data-mark-read]', function onMarkReadClick(event) {
        event.preventDefault();
        const notificationId = String($(this).data('notificationId') || '');
        if (!notificationId) return;

        request({
            url: resolveReadUrl(notificationId),
            method: 'PATCH',
        }).done(() => {
            fetchFeed();
        });
    });

    $markAll.on('click', (event) => {
        event.preventDefault();

        request({
            url: markAllUrl,
            method: 'PATCH',
        }).done(() => {
            fetchFeed();
        });
    });

    fetchFeed();
    setInterval(fetchFeed, Math.max(10000, pollIntervalMs));
};

$(bootNotificationCenter);
