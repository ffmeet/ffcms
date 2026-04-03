<?php

use App\Http\Controllers\Admin\QuickSearchController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\MemberActivityController;
use App\Http\Controllers\Web\MemberDashboardController;
use App\Http\Controllers\Web\MemberCommentController;
use App\Http\Controllers\Web\MemberProfileController;
use App\Http\Controllers\Web\MemberPostController;
use App\Http\Controllers\Web\PostCommentController;
use App\Http\Controllers\Web\PostController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('site.home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/member', MemberDashboardController::class)->name('member.dashboard');
    Route::get('/member/profile', [MemberProfileController::class, 'edit'])->name('member.profile.edit');
    Route::put('/member/profile', [MemberProfileController::class, 'update'])->name('member.profile.update');
    Route::get('/member/activity-center', [MemberActivityController::class, 'center'])->name('member.activity.center');
    Route::get('/member/activities', [MemberActivityController::class, 'index'])->name('member.activities.index');
    Route::get('/member/comments', [MemberCommentController::class, 'index'])->name('member.comments.index');
    Route::get('/member/posts', [MemberPostController::class, 'index'])->name('member.posts.index');
    Route::get('/member/posts/create', [MemberPostController::class, 'create'])->name('member.posts.create');
    Route::get('/member/posts/{post}/edit', [MemberPostController::class, 'edit'])->name('member.posts.edit');
    Route::post('/member/posts', [MemberPostController::class, 'store'])->name('member.posts.store');
    Route::put('/member/posts/{post}', [MemberPostController::class, 'update'])->name('member.posts.update');
    Route::post('/posts/{slug}/comments', [PostCommentController::class, 'store'])->name('posts.comments.store');
});

Route::get('/categories/{slug}', CategoryController::class)->name('categories.show');
Route::get('/posts/{slug}', PostController::class)->name('posts.show');
Route::get('/tags/{slug}', TagController::class)->name('tags.show');
Route::get('/search', SearchController::class)->name('search');

Route::get('/admin/quick-search', QuickSearchController::class)->name('admin.quick-search');
