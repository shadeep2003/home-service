@extends('layouts.app')
@section('title', 'Expert Home Services')
@section('content')
<section class="hero"><div><p class="eyebrow"><span class="eyebrow-line"></span> A LITTLE HELP. A BETTER HOME.</p><h1>Expert Home Services, <span>Right When You Need Them</span></h1><p class="lead">Life happens. So do leaky taps and dusty corners. Connect with trusted professionals who help you take care of the place you call home.</p><div class="hero-actions"><x-button :href="route('services')">Find a Service <span aria-hidden="true">↗</span></x-button><x-button :href="route('register', ['role' => 'provider'])" variant="secondary">Become a Provider</x-button></div><div class="hero-note"><span class="note-icon">✓</span><span>Built around your home. Designed around your day.</span></div></div><x-home-illustration /></section>
<div class="promise-strip"><span>Small fixes. Fresh starts. Everyday care.</span><span>⌂ &nbsp; For your home</span><span>✦ &nbsp; For your peace of mind</span></div>
<section class="section"><div class="section-top"><x-section-heading eyebrow="WHAT'S ON YOUR LIST?" title="A service for every corner" description="Explore our service categories. These are static previews of the future catalogue." /><a class="text-link" href="{{ route('services') }}">Explore services <span aria-hidden="true">→</span></a></div><x-service-grid /></section>
<x-how-it-works />
<section class="why-section"><div><p class="eyebrow">MORE CARE. LESS HASSLE.</p><h2>Your home matters.<br><span>So does your peace of mind.</span></h2><p class="lead">We’re building a thoughtful way for householders and professionals to connect, with clarity at every step.</p><a class="text-link" href="{{ route('about') }}">Meet our vision →</a></div><div class="benefits">
@foreach (['Trusted Professionals' => 'Our goal is to help you find skilled people you can feel confident inviting home.', 'Easy Booking' => 'A clear, convenient booking journey is planned for a future phase.', 'Secure Platform' => 'Account access is built around protected authentication.', 'Reliable Service' => 'Designed to support clear expectations between homes and professionals.'] as $title => $text)
<article><span class="benefit-icon"><x-icon name="check" /></span><h3>{{ $title }}</h3><p>{{ $text }}</p></article>
@endforeach
</div></section>
<section class="section"><x-section-heading eyebrow="SKILLED HANDS, FRIENDLY FACES" title="Meet the kind of professionals you’ll find" description="Demo profiles for design demonstration only. These are not real providers or bookable listings." /><div class="professional-grid">
@foreach ([['AM', 'Alex Morgan', 'Electrical specialist', 'navy'], ['JL', 'Jamie Lee', 'Home cleaning specialist', 'teal'], ['RK', 'Riley Kumar', 'Plumbing specialist', 'orange']] as [$initials, $name, $specialty, $color])
<article class="professional-card"><div class="portrait {{ $color }}"><span>{{ $initials }}</span><span class="demo-badge">DEMO PROFILE</span><span class="portrait-decoration" aria-hidden="true">✦</span></div><div class="professional-info"><h3>{{ $name }}</h3><p>{{ $specialty }}</p><span class="sample-label">Illustrative profile · Not available to book</span></div></article>
@endforeach
</div></section>
<section class="section testimonials"><x-section-heading eyebrow="A FEEL FOR THE EXPERIENCE" title="Less worry. More feeling at home." description="Placeholder testimonials for this UI preview; not actual customer feedback." /><div class="testimonial-grid">
@foreach (['A clear way to find help would make those little home repairs feel much more manageable.', 'I love the idea of finding the right professional without losing a whole afternoon.', 'A home that feels taken care of leaves more time for the things that matter.'] as $quote)
<figure><span class="quote-mark" aria-hidden="true">“</span><blockquote>{{ $quote }}</blockquote><figcaption><span class="quote-avatar">{{ $loop->iteration }}</span><span>Demo householder<small>Placeholder testimonial</small></span></figcaption></figure>
@endforeach
</div></section><x-cta />
@endsection
