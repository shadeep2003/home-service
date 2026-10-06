<?php
namespace App\Http\Controllers;
use App\Services\LoginVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class LoginVerificationController
{
    public function show(Request $request, LoginVerification $verification)
    {
        $challenge = $verification->current($request);
        if (! $challenge) {
            $request->session()->forget('pending_login');
            return redirect()->route('login')->withErrors(['email' => 'Your login request has ended. Please log in again.']);
        }
        return response()->view('auth.verify-login', compact('challenge'))->header('Cache-Control', 'no-store, private');
    }
    public function verify(Request $request, LoginVerification $verification)
    {
        // Do not flash OTPs back into session storage.
        $input = $request->input('code');
        $code = is_string($input) ? $input : '';
        if (! preg_match('/^[0-9]{6}$/D', $code)) {
            return redirect()->route('login.verify')->withErrors(['code' => 'Enter the six-digit code from your email.']);
        }
        $result = $verification->verify($request, $code);
        if (isset($result['error'])) { return redirect()->route('login.verify')->withErrors(['code' => $result['error']]); }
        $request->session()->forget('pending_login');
        Auth::login($result['user']);
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        return redirect()->intended(route('dashboard'))->with('status', 'Email verified successfully.');
    }
    public function resend(Request $request, LoginVerification $verification)
    {
        $result = $verification->resend($request);
        if (isset($result['status'])) { $request->session()->forget('errors'); }
        return isset($result['error'])
            ? redirect()->route('login.verify')->withErrors(['code' => $result['error']])
            : redirect()->route('login.verify')->with('status', $result['status']);
    }
    public function cancel(Request $request, LoginVerification $verification)
    {
        $verification->cancel($request);
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
