<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.site');
        $limited = fn ($request, $headers) => response()->view('auth.rate-limited', [
            'retryAfter' => $headers['Retry-After'] ?? 60,
            'returnUrl' => $request->session()->has('pending_login') ? route('login.verify') : route('login'),
        ], 429, $headers)->header('Cache-Control', 'no-store, private');
        \Illuminate\Support\Facades\RateLimiter::for('login', fn ($request) => [
            \Illuminate\Cache\RateLimiting\Limit::perMinute(6)->by('login-ip:'.$request->ip())->response($limited),
            \Illuminate\Cache\RateLimiting\Limit::perMinute(6)->by('login-account:'.hash('sha256', strtolower(trim((string) $request->input('email')))))->response($limited),
        ]);
        foreach (['otp-verify' => 10, 'otp-resend' => 3] as $name => $limit) {
            \Illuminate\Support\Facades\RateLimiter::for($name, fn ($request) => [
                \Illuminate\Cache\RateLimiting\Limit::perMinute($limit)->by($name.'-ip:'.$request->ip())->response($limited),
                \Illuminate\Cache\RateLimiting\Limit::perMinute($limit)->by($name.'-challenge:'.($request->session()->get('pending_login.id') ?? $request->session()->getId()))->response($limited),
            ]);
        }
    }
}
