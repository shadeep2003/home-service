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
    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = new User(collect($data)->only(['name', 'email', 'password'])->all());
        $user->role = Role::from($data['role']);
        $user->save();
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard');
    }
    public function login(LoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->validated())) {
            throw ValidationException::withMessages(['email' => 'The email or password is incorrect.']);
        }
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
