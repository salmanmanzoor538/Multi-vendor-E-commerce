import Chart from 'chart.js/auto';

const $ = window.jQuery;
const root = document.getElementById('admin-analytics-dashboard');

if (!$ || !root) {
    // This entrypoint is only for admin dashboard with jQuery loaded from master layout.
} else {
    const endpoint = root.dataset.endpoint;
    const defaultRange = root.dataset.defaultRange || '12m';

    const currencyFormatter = (currency = 'USD') => new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
    });

    const numberFormatter = new Intl.NumberFormat('en-US', {
        maximumFractionDigits: 0,
    });

    let currentCurrency = 'USD';
    let currentRange = defaultRange;
    const charts = {};

    const setActiveRangeButton = (range) => {
        $('.analytics-range-btn').removeClass('bg-gray-900 text-white border-gray-900').addClass('text-gray-700 border-gray-300');
        $(`.analytics-range-btn[data-range="${range}"]`)
            .addClass('bg-gray-900 text-white border-gray-900')
            .removeClass('text-gray-700 border-gray-300');
    };

    const setLoadingState = () => {
        $('#kpi-mrr').text('Loading...');
        $('#kpi-total-sales').text('Loading...');
        $('#kpi-orders-count').text('Loading...');
        $('#vendor-performance-table')
            .empty()
            .append(
                $('<tr>').append(
                    $('<td>', {
                        colspan: 3,
                        class: 'px-3 py-3 text-center text-gray-500',
                        text: 'Loading...',
                    }),
                ),
            );
    };

    const upsertChart = (key, elementId, config) => {
        if (charts[key]) {
            charts[key].destroy();
        }

        const canvas = document.getElementById(elementId);
        if (!canvas) {
            return;
        }

        charts[key] = new Chart(canvas, config);
    };

    const renderVendorTable = (rows) => {
        const $table = $('#vendor-performance-table');
        $table.empty();

        if (!Array.isArray(rows) || rows.length === 0) {
            $table.append(
                $('<tr>').append(
                    $('<td>', {
                        colspan: 3,
                        class: 'px-3 py-3 text-center text-gray-500',
                        text: 'No vendor performance data for this range.',
                    }),
                ),
            );
            return;
        }

        const money = currencyFormatter(currentCurrency);

        rows.forEach((row) => {
            $table.append(
                $('<tr>')
                    .append($('<td>', { class: 'px-3 py-2 text-gray-900', text: row.store_name ?? '' }))
                    .append($('<td>', { class: 'px-3 py-2 text-right text-gray-800', text: money.format(row.total_sales ?? 0) }))
                    .append($('<td>', { class: 'px-3 py-2 text-right text-gray-800', text: numberFormatter.format(row.orders_count ?? 0) })),
            );
        });
    };

    const renderCharts = (payload) => {
        const trends = payload.trends || {};
        const money = currencyFormatter(currentCurrency);

        upsertChart('mrr', 'chart-mrr', {
            type: 'line',
            data: {
                labels: trends.mrr_by_month?.labels || [],
                datasets: [{
                    label: 'MRR',
                    data: trends.mrr_by_month?.data || [],
                    borderColor: '#111827',
                    backgroundColor: 'rgba(17, 24, 39, 0.08)',
                    tension: 0.35,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => money.format(value),
                        },
                    },
                },
            },
        });

        upsertChart('sales', 'chart-sales', {
            type: 'bar',
            data: {
                labels: trends.sales_over_time?.labels || [],
                datasets: [{
                    label: 'Sales',
                    data: trends.sales_over_time?.data || [],
                    backgroundColor: 'rgba(37, 99, 235, 0.25)',
                    borderColor: '#2563eb',
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => money.format(value),
                        },
                    },
                },
            },
        });

        upsertChart('orders', 'chart-orders', {
            type: 'line',
            data: {
                labels: trends.orders_over_time?.labels || [],
                datasets: [{
                    label: 'Orders',
                    data: trends.orders_over_time?.data || [],
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.12)',
                    tension: 0.3,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
            },
        });

        upsertChart('status', 'chart-status-distribution', {
            type: 'doughnut',
            data: {
                labels: trends.order_status_distribution?.labels || [],
                datasets: [{
                    data: trends.order_status_distribution?.data || [],
                    backgroundColor: ['#2563eb', '#059669', '#d97706', '#dc2626', '#7c3aed', '#0d9488'],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    };

    const renderData = (payload) => {
        const kpis = payload.kpis || {};
        currentCurrency = payload.meta?.currency || 'USD';
        const money = currencyFormatter(currentCurrency);

        $('#kpi-mrr').text(money.format(kpis.mrr || 0));
        $('#kpi-total-sales').text(money.format(kpis.total_sales || 0));
        $('#kpi-orders-count').text(numberFormatter.format(kpis.orders_count || 0));

        renderVendorTable(payload.vendor_performance || []);
        renderCharts(payload);
    };

    const renderError = () => {
        $('#kpi-mrr').text('-');
        $('#kpi-total-sales').text('-');
        $('#kpi-orders-count').text('-');
        $('#vendor-performance-table')
            .empty()
            .append(
                $('<tr>').append(
                    $('<td>', {
                        colspan: 3,
                        class: 'px-3 py-3 text-center text-red-600',
                        text: 'Unable to load analytics. Please refresh.',
                    }),
                ),
            );
    };

    const fetchAndRender = async (range) => {
        setLoadingState();
        setActiveRangeButton(range);

        try {
            const response = await $.ajax({
                url: endpoint,
                method: 'GET',
                dataType: 'json',
                data: { range },
            });

            renderData(response);
        } catch (error) {
            renderError();
        }
    };

    $('.analytics-range-btn').on('click', function handleRangeClick() {
        currentRange = $(this).data('range');
        fetchAndRender(currentRange);
    });

    fetchAndRender(currentRange);
}
