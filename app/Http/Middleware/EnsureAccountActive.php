<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->suspended_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been suspended. Contact the administrator.']);
        }
        return $next($request);
    }
}
