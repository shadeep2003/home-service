<?php
namespace App\Http\Controllers;
use App\Enums\Role;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AuthController
{
    public function create(): \Illuminate\View\View
    {
        return view('auth.register', ['categories' => \App\Models\ServiceCategory::active()->orderBy('name')->get()]);
    }
    public function register(RegisterRequest $request, \App\Services\LoginVerification $verification): RedirectResponse
    {
        $data = $request->validated();
        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $user = new User(collect($data)->only(['name', 'email', 'password'])->all());
            $user->role = Role::from($data['role']);
            $user->save();
            if ($user->role === Role::Provider) {
                $user->providerProfile()->create(collect($data)->only(['phone', 'service_area', 'biography', 'experience_years', 'working_hours'])->all());
                $user->serviceCategories()->attach($data['category_ids']);
            }
            return $user;
        });
        return $this->requestVerification($request, $user, $verification);
    }
    public function login(LoginRequest $request, \App\Services\LoginVerification $verification): RedirectResponse
    {
        $credentials = $request->validated() + ['suspended_at' => null];
        // Validate without creating an authenticated session or remember cookie.
        $provider = Auth::guard()->getProvider();
        $user = $provider->retrieveByCredentials($credentials);
        if (! $user || ! $provider->validateCredentials($user, $credentials)) {
            // Use the same response for missing, suspended, and incorrect credentials.
            if (! $user) { \Illuminate\Support\Facades\Hash::check($credentials['password'], '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.'); }
            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }
        $provider->rehashPasswordIfRequired($user, $credentials);
        if ($user->email_verified_at) {
            $verification->cancel($request);
            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->regenerateToken();
            $request->session()->forget('errors');
            return redirect()->intended(route('dashboard'));
        }
        return $this->requestVerification($request, $user, $verification);
    }
    private function requestVerification(Request $request, User $user, \App\Services\LoginVerification $verification): RedirectResponse
    {
        $delivered = $verification->start($request, $user);
        if ($delivered) { $request->session()->forget('errors'); }
        return $delivered
            ? redirect()->route('login.verify')->with('status', 'A verification code has been sent to your account email.')
            : redirect()->route('login.verify')->withErrors(['code' => 'We could not send the verification email. Please retry after the countdown or return to login.']);
    }
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
