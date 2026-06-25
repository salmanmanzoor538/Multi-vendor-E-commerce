<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminAnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Customer\AddressController as CustomerAddressController;
use App\Http\Controllers\Customer\CartController as CustomerCartController;
use App\Http\Controllers\Customer\CheckoutController as CustomerCheckoutController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ReviewController as CustomerReviewController;
use App\Http\Controllers\CspReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Shop\CartController as ShopCartController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Vendor\CategoryController as VendorCategoryController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\AttributeController as VendorAttributeController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\Vendor\ProductVariantController as VendorProductVariantController;
use App\Http\Controllers\Vendor\ReviewController as VendorReviewController;
use App\Http\Controllers\Vendor\SubscriptionController as VendorSubscriptionController;
use App\Http\Controllers\Vendor\VendorController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index')->middleware('cart.access');
Route::get('/cart', [ShopCartController::class, 'index'])->name('cart.index')->middleware('cart.access');
Route::middleware('cart.access')->prefix('cart')->name('cart.')->group(function () {
    Route::get('/summary', [ShopCartController::class, 'summary'])->name('summary');
    Route::post('/items', [ShopCartController::class, 'store'])->name('items.store');
    Route::patch('/items/{lineKey}', [ShopCartController::class, 'update'])->name('items.update');
    Route::delete('/items/{lineKey}', [ShopCartController::class, 'destroy'])->name('items.destroy');
});

Route::get('/dashboard', function (Request $request): RedirectResponse {
    return redirect()->route($request->user()->dashboardRouteName());
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:'.UserRole::ADMIN->value])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/vendors', [AdminVendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/{vendor}', [AdminVendorController::class, 'show'])->name('vendors.show');
    Route::post('/vendors/{vendor}/approve', [AdminVendorController::class, 'approve'])->name('vendors.approve');
    Route::post('/vendors/{vendor}/reject', [AdminVendorController::class, 'reject'])->name('vendors.reject');
    Route::resource('categories', AdminCategoryController::class)->except('show');
    Route::resource('plans', AdminPlanController::class)->except('show');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status.update');
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/moderate', [AdminReviewController::class, 'moderate'])->name('reviews.moderate');
});

Route::get('/vendor/register', [VendorController::class, 'create'])->name('vendor.register.create');
Route::post('/vendor/register', [VendorController::class, 'store'])->name('vendor.register.store');

Route::middleware(['auth', 'role:'.UserRole::VENDOR->value, 'approved.vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/subscription', [VendorSubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription', [VendorSubscriptionController::class, 'store'])->name('subscription.store');
    Route::post('/subscription/renew', [VendorSubscriptionController::class, 'renew'])->name('subscription.renew');
    Route::post('/subscription/cancel', [VendorSubscriptionController::class, 'cancel'])->name('subscription.cancel');
});

Route::middleware(['auth', 'role:'.UserRole::VENDOR->value, 'approved.vendor', 'active.subscription'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', VendorDashboardController::class)->name('dashboard');
    Route::get('/store/edit', [VendorController::class, 'edit'])->name('store.edit');
    Route::patch('/store', [VendorController::class, 'update'])->name('store.update');
    Route::resource('products', VendorProductController::class);
    Route::resource('attributes', VendorAttributeController::class)->except('show');
    Route::post('/products/variants/generate', [VendorProductVariantController::class, 'generate'])->name('products.variants.generate');
    Route::post('/products/{product}/variants/sync', [VendorProductVariantController::class, 'sync'])->name('products.variants.sync');
    Route::resource('categories', VendorCategoryController::class)->except('show');
    Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [VendorOrderController::class, 'updateStatus'])->name('orders.status.update');
    Route::get('/reviews', [VendorReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/reply', [VendorReviewController::class, 'reply'])->name('reviews.reply');
});

Route::middleware(['auth', 'role:'.UserRole::CUSTOMER->value])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', CustomerDashboardController::class)->name('dashboard');
    Route::resource('addresses', CustomerAddressController::class)->except('show');
    Route::get('/cart', [CustomerCartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CustomerCartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/items/{cartItem}', [CustomerCartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items/{cartItem}', [CustomerCartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/checkout', [CustomerCheckoutController::class, 'showSummary'])->name('checkout.summary');
    Route::post('/checkout', [CustomerCheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/checkout/success', [CustomerCheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CustomerCheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/reviews', [CustomerReviewController::class, 'store'])->name('reviews.store');
});

Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');
Route::post('/csp/report', CspReportController::class)->middleware('throttle:120,1')->name('csp.report');
Route::middleware('auth')->post('/payments/{checkoutBatchId}/cod/settle', [PaymentController::class, 'settleCod'])
    ->name('payments.cod.settle');

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::patch('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
