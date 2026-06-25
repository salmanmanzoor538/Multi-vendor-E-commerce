<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-100 text-gray-900 antialiased">
        @php
            $authUser = auth()->user();
            $vendorEntity = $authUser?->ownedVendor ?? $authUser?->vendor;
            $hasActiveSubscription = $vendorEntity?->activeSubscription
                && $vendorEntity->activeSubscription->ends_at
                && $vendorEntity->activeSubscription->ends_at->isFuture();
        @endphp
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="text-lg font-semibold tracking-tight">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <nav class="flex items-center gap-4 text-sm font-medium">
                    @guest
                        <a href="{{ route('shop.index') }}" class="text-gray-600 hover:text-gray-900">Shop</a>
                        <a href="{{ route('cart.index') }}" class="text-gray-600 hover:text-gray-900">Cart</a>
                    @endguest
                    @auth
                        @if (auth()->user()->hasRole(\App\Enums\UserRole::CUSTOMER))
                            <a href="{{ route('shop.index') }}" class="text-gray-600 hover:text-gray-900">Shop</a>
                            <a href="{{ route('cart.index') }}" class="text-gray-600 hover:text-gray-900">Cart</a>
                        @endif
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        @if (auth()->user()->hasRole(\App\Enums\UserRole::ADMIN))
                            <a href="{{ route('admin.vendors.index') }}" class="text-gray-600 hover:text-gray-900">Vendors</a>
                            <a href="{{ route('admin.plans.index') }}" class="text-gray-600 hover:text-gray-900">Plans</a>
                            <a href="{{ route('admin.categories.index') }}" class="text-gray-600 hover:text-gray-900">Categories</a>
                            <a href="{{ route('admin.orders.index') }}" class="text-gray-600 hover:text-gray-900">Orders</a>
                            <a href="{{ route('admin.reviews.index') }}" class="text-gray-600 hover:text-gray-900">Reviews</a>
                        @endif
                        @if (auth()->user()->hasRole(\App\Enums\UserRole::VENDOR) && auth()->user()->hasApprovedVendorAccess())
                            <a href="{{ route('vendor.subscription.index') }}" class="text-gray-600 hover:text-gray-900">Subscription</a>
                            @if ($hasActiveSubscription)
                                <a href="{{ route('vendor.store.edit') }}" class="text-gray-600 hover:text-gray-900">Store</a>
                                <a href="{{ route('vendor.products.index') }}" class="text-gray-600 hover:text-gray-900">Products</a>
                                <a href="{{ route('vendor.attributes.index') }}" class="text-gray-600 hover:text-gray-900">Attributes</a>
                                <a href="{{ route('vendor.categories.index') }}" class="text-gray-600 hover:text-gray-900">Categories</a>
                                <a href="{{ route('vendor.orders.index') }}" class="text-gray-600 hover:text-gray-900">Orders</a>
                                <a href="{{ route('vendor.reviews.index') }}" class="text-gray-600 hover:text-gray-900">Reviews</a>
                            @endif
                        @endif
                        @if (auth()->user()->hasRole(\App\Enums\UserRole::CUSTOMER))
                            <a href="{{ route('customer.cart.index') }}" class="text-gray-600 hover:text-gray-900">My Cart</a>
                            <a href="{{ route('customer.checkout.summary') }}" class="text-gray-600 hover:text-gray-900">Checkout</a>
                            <a href="{{ route('customer.orders.index') }}" class="text-gray-600 hover:text-gray-900">Orders</a>
                            <a href="{{ route('customer.addresses.index') }}" class="text-gray-600 hover:text-gray-900">Addresses</a>
                        @endif
                        <div
                            class="relative"
                            data-notification-center
                            data-feed-url="{{ route('notifications.index') }}"
                            data-mark-read-template="{{ route('notifications.read', ['notification' => '__NOTIFICATION_ID__']) }}"
                            data-mark-all-url="{{ route('notifications.read-all') }}"
                            data-poll-interval-ms="{{ (int) config('notifications.order_updates.poll_interval_seconds', 30) * 1000 }}"
                            data-feed-limit="{{ (int) config('notifications.order_updates.feed_limit', 10) }}"
                        >
                            <button
                                type="button"
                                class="relative rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50"
                                data-notification-toggle
                            >
                                Notifications
                                <span
                                    class="absolute -right-2 -top-2 hidden rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-semibold text-white"
                                    data-unread-badge
                                >
                                    0
                                </span>
                            </button>

                            <div
                                class="absolute right-0 z-50 mt-2 hidden w-96 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
                                data-notification-panel
                            >
                                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                                    <p class="text-sm font-semibold text-gray-900">Notifications</p>
                                    <button
                                        type="button"
                                        class="rounded-md border border-gray-300 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                                        data-mark-all-read
                                    >
                                        Mark all read
                                    </button>
                                </div>
                                <ul class="max-h-96 overflow-y-auto" data-notification-list></ul>
                                <p class="hidden p-4 text-sm text-gray-600" data-notification-empty>No notifications found.</p>
                            </div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-gray-900">Profile</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-700">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="{{ route('register') }}" class="text-gray-600 hover:text-gray-900">Register</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            @yield('content')
        </main>

        @stack('scripts')
    </body>
</html>
