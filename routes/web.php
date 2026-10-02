<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/services', [PublicPageController::class, 'services'])->name('services');
Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::middleware('guest')->group(function () {
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function (Request $request) {
        return redirect()->route($request->user()->role->value.'.dashboard');
    })->name('dashboard');
    foreach (['customer', 'provider', 'admin'] as $role) {
        Route::view('/'.$role.'/dashboard', 'dashboard.'.$role)
            ->middleware('role:'.$role)->name($role.'.dashboard');
    }
});
