<?php

namespace App\Providers;

use App\Models\Vendor;
use App\Models\Category;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Attribute;
use App\Models\Plan;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use App\Policies\CategoryPolicy;
use App\Policies\CartPolicy;
use App\Policies\CartItemPolicy;
use App\Policies\CustomerAddressPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\AttributePolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductVariantPolicy;
use App\Policies\PlanPolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\VendorPolicy;
use App\Policies\NotificationPolicy;
use App\Repositories\Contracts\CartItemRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\AdminAnalyticsRepositoryInterface;
use App\Repositories\Contracts\AttributeRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CustomerAddressRepositoryInterface;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OrderStatusHistoryRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\PlanRepositoryInterface;
use App\Repositories\Contracts\SubscriptionRepositoryInterface;
use App\Repositories\Contracts\VendorRepositoryInterface;
use App\Repositories\Eloquent\CartItemRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\AdminAnalyticsRepository;
use App\Repositories\Eloquent\AttributeRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CustomerAddressRepository;
use App\Repositories\Eloquent\OrderItemRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\OrderStatusHistoryRepository;
use App\Repositories\Eloquent\PaymentRepository;
use App\Repositories\Eloquent\ProductImageRepository;
use App\Repositories\Eloquent\NotificationRepository;
use App\Repositories\Eloquent\ProductVariantRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ReviewRepository;
use App\Repositories\Eloquent\PlanRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Eloquent\VendorRepository;
use App\Models\Subscription;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\DatabaseNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(VendorRepositoryInterface::class, VendorRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductImageRepositoryInterface::class, ProductImageRepository::class);
        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
        $this->app->bind(ProductVariantRepositoryInterface::class, ProductVariantRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(CartItemRepositoryInterface::class, CartItemRepository::class);
        $this->app->bind(CustomerAddressRepositoryInterface::class, CustomerAddressRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderStatusHistoryRepositoryInterface::class, OrderStatusHistoryRepository::class);
        $this->app->bind(OrderItemRepositoryInterface::class, OrderItemRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(AdminAnalyticsRepositoryInterface::class, AdminAnalyticsRepository::class);
        $this->app->bind(PlanRepositoryInterface::class, PlanRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Gate::policy(Vendor::class, VendorPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Attribute::class, AttributePolicy::class);
        Gate::policy(ProductVariant::class, ProductVariantPolicy::class);
        Gate::policy(Cart::class, CartPolicy::class);
        Gate::policy(CustomerAddress::class, CustomerAddressPolicy::class);
        Gate::policy(CartItem::class, CartItemPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Subscription::class, SubscriptionPolicy::class);
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);
    }
}
