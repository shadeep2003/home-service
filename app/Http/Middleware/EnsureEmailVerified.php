<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class EnsureEmailVerified
{
    public function handle(Request $request, Closure $next)
    {
        // Recheck persisted status so sessions established before an email change
        // or before this policy cannot bypass email verification.
        if ($request->user() && ! $request->user()->fresh()?->email_verified_at && ! $request->routeIs('logout')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Email verification is required. Log in to verify your email.'], 403);
            }
            return redirect()->route('login')->with('status', 'Please log in to verify your email before continuing.');
        }
        return $next($request);
    }
}
