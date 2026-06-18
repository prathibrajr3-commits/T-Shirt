<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminPromotionController;
use App\Http\Controllers\Admin\AdminShippingController;
use App\Http\Controllers\Admin\AdminSiteSettingController;

// Storefront
Route::get('/', [ShopController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'shop'])->name('shop.list');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.show');

// Custom Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Session-based Shopping Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{key}', [CartController::class, 'remove'])->name('cart.remove');

// Authenticated Customer Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/razorpay-callback', [CheckoutController::class, 'razorpayCallback'])->name('checkout.razorpay-callback');
    
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    
    Route::post('/products/{productId}/review', [ReviewController::class, 'store'])->name('reviews.store');
});

// Admin Panel routes (Requires auth and role = admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    
    // Categories CRUD
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
    
    // Products CRUD
    Route::resource('products', AdminProductController::class)->names([
        'index' => 'admin.products.index',
        'create' => 'admin.products.create',
        'store' => 'admin.products.store',
        'edit' => 'admin.products.edit',
        'update' => 'admin.products.update',
        'destroy' => 'admin.products.destroy',
    ])->except(['show']);
    
    // Orders Management
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::put('/orders/{id}', [AdminOrderController::class, 'update'])->name('admin.orders.update');

    // Banners Management
    Route::resource('banners', AdminBannerController::class)->names([
        'index' => 'admin.banners.index',
        'create' => 'admin.banners.create',
        'store' => 'admin.banners.store',
        'edit' => 'admin.banners.edit',
        'update' => 'admin.banners.update',
        'destroy' => 'admin.banners.destroy',
    ]);

    // Promotion Banners Management
    Route::resource('promotions', AdminPromotionController::class)->names([
        'index'   => 'admin.promotions.index',
        'create'  => 'admin.promotions.create',
        'store'   => 'admin.promotions.store',
        'edit'    => 'admin.promotions.edit',
        'update'  => 'admin.promotions.update',
        'destroy' => 'admin.promotions.destroy',
    ])->except(['show']);
    Route::post('/promotions/{id}/toggle', [AdminPromotionController::class, 'toggleActive'])->name('admin.promotions.toggle');
    Route::get('/promotions/{id}/preview', [AdminPromotionController::class, 'preview'])->name('admin.promotions.preview');

    // Site Settings (Unified branding, footer, contacts, socials)
    Route::get('/site-settings', [AdminSiteSettingController::class, 'index'])->name('admin.site-settings.index');
    Route::put('/site-settings', [AdminSiteSettingController::class, 'update'])->name('admin.site-settings.update');

    // Site Settings Quick Links CRUD & Reorder
    Route::post('/site-settings/links', [AdminSiteSettingController::class, 'storeLink'])->name('admin.site-settings.links.store');
    Route::put('/site-settings/links/{id}', [AdminSiteSettingController::class, 'updateLink'])->name('admin.site-settings.links.update');
    Route::delete('/site-settings/links/{id}', [AdminSiteSettingController::class, 'destroyLink'])->name('admin.site-settings.links.destroy');
    Route::post('/site-settings/links/reorder', [AdminSiteSettingController::class, 'reorderLinks'])->name('admin.site-settings.links.reorder');

    // Shipping Settings
    Route::get('/shipping', [AdminShippingController::class, 'edit'])->name('admin.shipping.edit');
    Route::put('/shipping', [AdminShippingController::class, 'update'])->name('admin.shipping.update');
});
