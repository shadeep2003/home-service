<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/services', [PublicPageController::class, 'services'])->name('services');
Route::get('/services/{category:slug}', [PublicPageController::class, 'category'])->name('services.category');
Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'create'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function (Request $request) {
        return redirect()->route($request->user()->role->value.'.dashboard');
    })->name('dashboard');
    foreach (['customer'] as $role) {
        Route::view('/'.$role.'/dashboard', 'dashboard.'.$role)
            ->middleware('role:'.$role)->name($role.'.dashboard');
    }
});

Route::middleware(['auth', 'role:provider'])->group(function () {
    Route::get('/provider/dashboard', [\App\Http\Controllers\ProviderProfileController::class, 'dashboard'])->name('provider.dashboard');
    Route::get('/provider/profile/edit', [\App\Http\Controllers\ProviderProfileController::class, 'edit'])->name('provider.profile.edit');
    Route::put('/provider/profile', [\App\Http\Controllers\ProviderProfileController::class, 'update'])->name('provider.profile.update');
});
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', \App\Http\Controllers\ServiceCategoryController::class)->parameters(['categories' => 'category'])->except(['show', 'destroy']);
});

Route::get('/providers/{provider}/reviews', [\App\Http\Controllers\ProviderReviewController::class, 'show'])->name('providers.reviews');
Route::post('/providers/{provider}/reviews', [\App\Http\Controllers\ProviderReviewController::class, 'store'])->middleware(['auth', 'role:customer', 'throttle:10,1'])->name('providers.reviews.store');

Route::post('/profile/photo', [\App\Http\Controllers\ProfilePhotoController::class, 'update'])->middleware(['auth', 'throttle:10,1'])->name('profile.photo.update');
Route::get('/profile/photos/{user}', [\App\Http\Controllers\ProfilePhotoController::class, 'show'])->name('profile.photo.show');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::patch('/admin/accounts/{user}', [\App\Http\Controllers\AdminDashboardController::class, 'update'])->name('admin.accounts.update');
});
