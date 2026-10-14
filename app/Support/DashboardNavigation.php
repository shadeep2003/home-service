<?php
namespace App\Support;
use App\Enums\Role;
use Illuminate\Http\Request;
use App\Models\User;
class DashboardNavigation
{
    /** Navigation is presentation only; route middleware continues enforcing access. */
    public static function groups(User $user, Request $request): array
    {
        $item = fn (string $label, string $icon, string $url, bool $active = false, ?string $section = null) => compact('label', 'icon', 'url', 'active', 'section');
        $account = ['label' => 'Account', 'items' => [
            $item($user->role === Role::Provider ? 'Edit provider profile' : 'My profile', 'profile',
                route($user->role === Role::Provider ? 'provider.profile.edit' : 'profile.edit'),
                $request->routeIs($user->role === Role::Provider ? 'provider.profile.*' : 'profile.*')),
        ]];
        return match ($user->role) {
            Role::Customer => [
                ['label' => 'Overview', 'items' => [$item('Dashboard', 'dashboard', route('customer.dashboard'), $request->routeIs('customer.dashboard'))]],
                ['label' => 'Services', 'items' => [$item('Browse services', 'services', route('services'), $request->routeIs('services*'))]],
                $account,
            ],
            Role::Provider => [
                ['label' => 'Overview', 'items' => [$item('Provider dashboard', 'dashboard', route('provider.dashboard'), $request->routeIs('provider.dashboard'))]],
                ['label' => 'Service management', 'items' => [
                    $item('My services', 'services', route('provider.subcategories.index'), $request->routeIs('provider.subcategories.*')),
                    $item('Browse marketplace', 'categories', route('services'), $request->routeIs('services*')),
                ]],
                $account,
            ],
            Role::Admin => self::adminGroups($request, $item, $account),
        };
    }
    private static function adminGroups(Request $request, \Closure $item, array $account): array
    {
        $onDashboard = $request->routeIs('admin.dashboard');
        $providers = $request->query('role') === 'provider';
        $filtered = $request->filled('search') || $request->filled('role') || $request->filled('status') || $request->filled('page');
        return [
            ['label' => 'Overview', 'items' => [
                $item('Admin dashboard', 'dashboard', route('admin.dashboard'), $onDashboard && !$filtered, $onDashboard ? 'overview' : null),
            ]],
            ['label' => 'Management', 'items' => [
                // These link to the existing account table and its existing provider filter.
                $item('User management', 'users', route('admin.dashboard').'#accounts', $onDashboard && $filtered && !$providers, $onDashboard ? 'accounts' : null),
                $item('Service providers', 'provider', route('admin.dashboard', ['role' => 'provider']).'#accounts', $onDashboard && $providers),
                $item('Service categories', 'categories', route('admin.categories.index'), $request->routeIs('admin.categories.*')),
            ]],
            $account,
        ];
    }
}
