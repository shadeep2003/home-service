<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <nav class="container navigation" aria-label="Main navigation">
        <a class="brand" href="{{ route('home') }}" aria-label="HomeServices home">
            <span class="brand-mark" aria-hidden="true">⌂</span>
            <span>Home<span class="brand-teal">Services</span><small>A LITTLE HELP. A BETTER HOME.</small></span>
        </a>

        <div class="nav-links" id="desktop-navigation">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            <a href="{{ route('services') }}" @if(request()->routeIs('services*')) aria-current="page" @endif>Services</a>
            <a href="{{ route('home') }}#how-it-works">How It Works</a>
            <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>About Us</a>
            <a href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
        </div>

        <div class="nav-actions nav-actions-desktop">
            @auth
                @php($navUser = auth()->user())
                @php($navRole = $navUser->role->value)
                <details class="account-menu">
                    <summary aria-label="Account menu for {{ $navUser->name }}">
                        @if($navUser->profile_photo_path)
                            <img class="account-avatar" src="{{ $navUser->profilePhotoUrl() }}" alt="" width="38" height="38">
                        @else
                            <span class="account-avatar account-avatar-initials" aria-hidden="true">{{ collect(explode(' ', trim($navUser->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') }}</span>
                        @endif
                        <span class="account-name">{{ Str::of($navUser->name)->explode(' ')->first() }}<small>{{ ucfirst($navRole) }} account</small></span>
                        <span class="account-chevron" aria-hidden="true">⌄</span>
                    </summary>
                    <div class="account-dropdown">
                        <a href="{{ route($navRole.'.dashboard') }}">{{ ucfirst($navRole) }} dashboard</a>
                        @if($navRole === 'customer')
                            <a href="{{ route('services') }}">Browse services</a>
                        @elseif($navRole === 'provider')
                            <a href="{{ route('provider.profile.edit') }}">Professional profile</a>
                        @elseif($navRole === 'admin')
                            <a href="{{ route('admin.categories.index') }}">Manage services</a>
                        @endif
                        <a href="{{ route('profile.edit') }}">My profile</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Log out</button></form>
                    </div>
                </details>
            @else
                <a class="nav-login" href="{{ route('login') }}" @if(request()->routeIs('login')) aria-current="page" @endif>Log in</a>
                <a class="button nav-cta" href="{{ route('register') }}">Get started <span aria-hidden="true">↗</span></a>
            @endauth
        </div>

        <button class="mobile-menu-toggle" type="button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="mobile-navigation" data-nav-open>
            <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
        </button>

        <div class="mobile-nav-panel" id="mobile-navigation">
            <div class="mobile-nav-heading">
                <span>Explore HomeServices</span>
                <button type="button" class="mobile-menu-close" aria-label="Close navigation menu" data-nav-close>×</button>
            </div>
            <div class="mobile-nav-links">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif><span>Home</span><span aria-hidden="true">↗</span></a>
                <a href="{{ route('services') }}" @if(request()->routeIs('services*')) aria-current="page" @endif><span>Services</span><span aria-hidden="true">↗</span></a>
                <a href="{{ route('home') }}#how-it-works"><span>How It Works</span><span aria-hidden="true">↗</span></a>
                <a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif><span>About Us</span><span aria-hidden="true">↗</span></a>
                <a href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif><span>Contact</span><span aria-hidden="true">↗</span></a>
            </div>
            <div class="mobile-nav-account">
                @auth
                    <div class="mobile-account-identity">
                        @if($navUser->profile_photo_path)
                            <img class="account-avatar" src="{{ $navUser->profilePhotoUrl() }}" alt="" width="42" height="42">
                        @else
                            <span class="account-avatar account-avatar-initials" aria-hidden="true">{{ collect(explode(' ', trim($navUser->name)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') }}</span>
                        @endif
                        <span>{{ $navUser->name }}<small>{{ ucfirst($navRole) }} account</small></span>
                    </div>
                    <a href="{{ route($navRole.'.dashboard') }}">{{ ucfirst($navRole) }} dashboard</a>
                    @if($navRole === 'customer')
                        <a href="{{ route('services') }}">Browse services</a>
                    @elseif($navRole === 'provider')
                        <a href="{{ route('provider.profile.edit') }}">Professional profile</a>
                    @elseif($navRole === 'admin')
                        <a href="{{ route('admin.categories.index') }}">Manage services</a>
                    @endif
                    <a href="{{ route('profile.edit') }}">My profile</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="mobile-logout">Log out</button></form>
                @else
                    <a href="{{ route('login') }}" class="mobile-login">Log in</a>
                    <a href="{{ route('register') }}" class="button nav-cta">Get started <span aria-hidden="true">↗</span></a>
                @endauth
            </div>
        </div>
    </nav>
</header>
