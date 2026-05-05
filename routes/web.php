<?php

use App\Http\Controllers\Admin\QuickSearchController;
use App\Http\Controllers\Admin\StaffProfileController;
use App\Http\Controllers\Web\AuthorController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CommerceActionController;
use App\Http\Controllers\Web\DeveloperDocsController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\MemberActivityController;
use App\Http\Controllers\Web\MemberDashboardController;
use App\Http\Controllers\Web\MemberCommentController;
use App\Http\Controllers\Web\MemberOrderController;
use App\Http\Controllers\Web\MemberProfileController;
use App\Http\Controllers\Web\MemberPostController;
use App\Http\Controllers\Web\MemberSubscriptionController;
use App\Http\Controllers\Web\PaymentWebhookController;
use App\Http\Controllers\Web\PostCommentController;
use App\Http\Controllers\Web\PostController;
use App\Http\Controllers\Web\PricingController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\ShopController;
use App\Http\Controllers\Web\TagController;
use App\Http\Controllers\Web\ThemePreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('site.home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('auth.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,10')->name('auth.register');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,10')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,10')->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::middleware('member.permission:member.center')->group(function (): void {
        Route::get('/member', MemberDashboardController::class)->name('member.dashboard');
        Route::get('/member/profile', [MemberProfileController::class, 'edit'])->name('member.profile.edit');
        Route::put('/member/profile', [MemberProfileController::class, 'update'])->name('member.profile.update');
        Route::get('/member/activity-center', [MemberActivityController::class, 'center'])->name('member.activity.center');
        Route::get('/member/activities', [MemberActivityController::class, 'index'])->name('member.activities.index');
        Route::get('/member/comments', [MemberCommentController::class, 'index'])->name('member.comments.index');
        Route::get('/member/orders', [MemberOrderController::class, 'index'])->name('member.orders.index');
        Route::get('/member/orders/{order}/pay', [MemberOrderController::class, 'pay'])->name('member.orders.pay');
        Route::post('/member/orders/{order}/pay', [MemberOrderController::class, 'simulatePayment'])->name('member.orders.simulate-payment');
        Route::get('/member/posts', [MemberPostController::class, 'index'])->name('member.posts.index');
        Route::get('/member/posts/create', [MemberPostController::class, 'create'])->name('member.posts.create');
        Route::get('/member/posts/{post}/edit', [MemberPostController::class, 'edit'])->name('member.posts.edit');
        Route::post('/member/posts', [MemberPostController::class, 'store'])->name('member.posts.store');
        Route::put('/member/posts/{post}', [MemberPostController::class, 'update'])->name('member.posts.update');
        Route::get('/member/subscriptions', [MemberSubscriptionController::class, 'index'])->name('member.subscriptions.index');
    });
    Route::post('/shop/{slug}/purchase', [CommerceActionController::class, 'purchaseProduct'])
        ->middleware('member.permission:shop.access')
        ->name('shop.purchase');
    Route::post('/pricing/{slug}/subscribe', [CommerceActionController::class, 'subscribe'])
        ->middleware('member.permission:member.center')
        ->name('pricing.subscribe');
    Route::post('/events/{slug}/register', [CommerceActionController::class, 'registerEvent'])
        ->middleware('member.permission:events.access')
        ->name('events.register');
    Route::post('/posts/{slug}/comments', [PostCommentController::class, 'store'])->name('posts.comments.store');
    Route::middleware('member.permission:admin.access')->group(function (): void {
        Route::get('/preview/theme/reset', [ThemePreviewController::class, 'reset'])->name('preview.theme.reset');
        Route::get('/preview/theme/{theme}', [ThemePreviewController::class, 'preview'])->name('preview.theme');
    });
    Route::put('/admin/staff-profile', [StaffProfileController::class, 'update'])->name('admin.staff-profile.update');
});

Route::get('/categories/{slug}', CategoryController::class)->name('categories.show');
Route::get('/developer-docs/{page?}', DeveloperDocsController::class)
    ->where('page', '.*')
    ->name('developer.docs');
Route::get('/authors/{username}', [AuthorController::class, 'show'])->name('authors.show');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
Route::get('/pricing', PricingController::class)->name('pricing');
Route::post('/payments/webhooks/{provider}', [PaymentWebhookController::class, 'handle'])->name('payments.webhook');
Route::get('/posts/{slug}', PostController::class)->name('posts.show');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/tags/{slug}', TagController::class)->name('tags.show');
Route::get('/search', SearchController::class)->name('search');

Route::get('/admin/quick-search', QuickSearchController::class)->name('admin.quick-search');
