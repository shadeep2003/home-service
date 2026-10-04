<?php
namespace App\Http\Controllers;
use App\Enums\Role;
use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\ProviderReview;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
class AdminDashboardController
{
    public function index(Request $request)
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'role' => ['nullable', 'in:customer,provider'], 'status' => ['nullable', 'in:active,suspended']]);
        $users = User::whereIn('role', ['customer', 'provider'])->with('providerProfile');
        if (!empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $users->where(fn ($query) => $query->where('name', 'like', $term)->orWhere('email', 'like', $term));
        }
        if (!empty($filters['role'])) { $users->where('role', $filters['role']); }
        if (($filters['status'] ?? '') === 'active') { $users->whereNull('suspended_at'); }
        if (($filters['status'] ?? '') === 'suspended') { $users->whereNotNull('suspended_at'); }
        return view('dashboard.admin', [
            'charts' => $this->charts(),
            'users' => $users->latest()->orderByDesc('id')->paginate(12)->withQueryString(),
            'stats' => [
                'Customers' => User::where('role', 'customer')->count(),
                'Providers' => User::where('role', 'provider')->count(),
                'Suspended accounts' => User::whereNotNull('suspended_at')->count(),
                'Active services' => ServiceCategory::active()->count(),
                'Customer reviews' => ProviderReview::count(),
                'Providers working' => ProviderProfile::where('is_working', true)->whereHas('user', fn ($query) => $query->whereNull('suspended_at')->where('role', 'provider'))->count(),
            ],
        ]);
    }
    private function charts(): array
    {
        $accounts = User::whereIn('role', ['customer', 'provider']);
        $profiles = ProviderProfile::whereHas('user', fn ($query) => $query->whereNull('suspended_at')->where('role', 'provider'));
        return [
            'Account types' => [
                ['label' => 'Customers', 'value' => User::where('role', 'customer')->count(), 'color' => '#087f82'],
                ['label' => 'Providers', 'value' => User::where('role', 'provider')->count(), 'color' => '#123047'],
            ],
            'Account status' => [
                ['label' => 'Active', 'value' => (clone $accounts)->whereNull('suspended_at')->count(), 'color' => '#087f82'],
                ['label' => 'Suspended', 'value' => (clone $accounts)->whereNotNull('suspended_at')->count(), 'color' => '#b7672d'],
            ],
            'Active provider availability' => [
                ['label' => 'Available', 'value' => (clone $profiles)->where('is_working', false)->where('is_available', true)->count(), 'color' => '#087f82'],
                ['label' => 'Working', 'value' => (clone $profiles)->where('is_working', true)->count(), 'color' => '#123047'],
                ['label' => 'Unavailable', 'value' => (clone $profiles)->where('is_working', false)->where('is_available', false)->count(), 'color' => '#b7672d'],
            ],
        ];
    }
    public function update(Request $request, User $user)
    {
        abort_unless(in_array($user->role, [Role::Customer, Role::Provider], true) && $user->id !== $request->user()->id, 403);
        $data = $request->validate(['action' => ['required', 'in:suspend,reactivate']]);
        $user->forceFill(['suspended_at' => $data['action'] === 'suspend' ? now() : null])->save();
        return back()->with('status', $data['action'] === 'suspend' ? 'Account suspended.' : 'Account reactivated.');
    }
}
