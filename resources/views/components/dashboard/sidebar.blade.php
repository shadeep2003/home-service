@props(['user'])
@php
    $roleLabel = match ($user->role) {
        \App\Enums\Role::Provider => 'Service provider',
        \App\Enums\Role::Admin => 'Administrator',
        default => 'Customer',
    };
    $profileUrl = route($user->role === \App\Enums\Role::Provider ? 'provider.profile.edit' : 'profile.edit');
@endphp
<aside class="dashboard-sidebar" id="dashboard-sidebar" aria-label="{{ $roleLabel }} sidebar" data-dashboard-sidebar>
    <div class="dashboard-sidebar__brand-row">
        <a class="brand dashboard-brand" href="{{ route('home') }}" aria-label="HomeServices home" title="HomeServices home">
            <span class="brand-mark" aria-hidden="true">⌂</span>
            <span class="dashboard-brand__text">Home<span class="brand-teal">Services</span><small>A LITTLE HELP. A BETTER HOME.</small></span>
        </a>
        <button class="dashboard-sidebar-close" type="button" aria-label="Close workspace navigation" data-dashboard-close><x-icon name="close" /></button>
    </div>
    <div class="dashboard-sidebar__workspace"><span class="dashboard-sidebar__workspace-dot" aria-hidden="true"></span><span class="dashboard-sidebar-label">{{ $roleLabel }} workspace</span></div>
    <nav class="dashboard-sidebar__navigation" aria-label="{{ $roleLabel }} workspace navigation">
        @foreach(\App\Support\DashboardNavigation::groups($user, request()) as $group)
            <x-dashboard.sidebar-section :group="$group" />
        @endforeach
    </nav>
    <div class="dashboard-sidebar__bottom">
        <a class="dashboard-user-card" href="{{ $profileUrl }}" aria-label="Edit profile for {{ $user->name }}" title="{{ $user->name }} · {{ $roleLabel }}">
            @if($user->profile_photo_path)
                <img class="dashboard-user-card__avatar" src="{{ $user->profilePhotoUrl() }}" width="40" height="40" alt="" decoding="async">
            @else
                <span class="dashboard-user-card__avatar dashboard-user-card__initials" aria-hidden="true">{{ collect(preg_split('/\s+/u', trim($user->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') }}</span>
            @endif
            <span class="dashboard-user-card__details"><strong>{{ $user->name }}</strong><small>{{ $roleLabel }}</small></span>
            <x-icon name="chevron-right" class="dashboard-user-card__chevron" />
        </a>
        <form method="POST" action="{{ route('logout') }}" class="dashboard-logout">@csrf
            <button class="dashboard-nav-item" type="submit" aria-label="Log out" title="Log out"><x-icon name="logout" class="dashboard-nav-item__icon" /><span class="dashboard-sidebar-label">Log out</span></button>
        </form>
    </div>
</aside>
