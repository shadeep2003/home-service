<footer class="footer"><div class="container"><div class="footer-grid">
<div><a class="brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">⌂</span>HomeServices</a><p>A little help for the place you love.<br>Connecting homes with skilled hands.</p><span class="footer-tag">Made for everyday life.</span></div>
<div><h2>Explore</h2><a href="{{ route('services') }}">Services</a><a href="{{ route('home') }}#how-it-works">How It Works</a><a href="{{ route('about') }}">About Us</a><a href="{{ route('contact') }}">Contact</a></div>
<div><h2>Your next step</h2><a href="{{ route('login') }}">Login</a><a href="{{ route('register') }}">Create an account</a><a href="{{ route('register', ['role' => 'provider']) }}">Become a Provider</a></div>
<div><h2>A platform taking shape</h2><p>Browse real service categories and registered providers. Booking will arrive in a later phase.</p></div>
</div><div class="footer-bottom"><span>© {{ date('Y') }} HomeServices. All rights reserved.</span><span>Thoughtful care. From doorstep to done.</span></div></div></footer>


