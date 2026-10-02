<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header"><nav class="container navigation" aria-label="Main navigation">
<a class="brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">⌂</span><span>Home<span class="brand-teal">Services</span><small>A LITTLE HELP. A BETTER HOME.</small></span></a>
<details class="nav-menu" open><summary>Menu <span aria-hidden="true">☰</span></summary><div class="nav-links">
@foreach (['home' => 'Home', 'services' => 'Services'] as $name => $label)
<a href="{{ route($name) }}" @if(request()->routeIs($name)) aria-current="page" @endif>{{ $label }}</a>
@endforeach
<a href="{{ route('home') }}#how-it-works">How It Works</a>
@foreach (['about' => 'About Us', 'contact' => 'Contact'] as $name => $label)
<a href="{{ route($name) }}" @if(request()->routeIs($name)) aria-current="page" @endif>{{ $label }}</a>
@endforeach
<div class="nav-actions">@auth
<a href="{{ route('dashboard') }}">Dashboard</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="button secondary" type="submit">Log out</button></form>
@else<a href="{{ route('login') }}">Login</a><x-button :href="route('register')">Register <span aria-hidden="true">↗</span></x-button>@endauth</div>
</div></details></nav></header>
