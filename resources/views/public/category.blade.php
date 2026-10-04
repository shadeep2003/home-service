@extends('layouts.app')
@section('title', $category->name.' providers')
@section('content')
<section class="page-intro"><a href="{{ route('services') }}">← All services</a><p class="eyebrow">LOCAL PROFESSIONALS</p><h1>{{ $category->name }} providers</h1><p class="lead">{{ $category->description }}</p></section>
<div class="service-grid provider-grid">
@forelse ($providers as $provider)
@php($profile = $provider->providerProfile)
<article class="provider-card">
    <div class="provider-card-top">
        <x-profile-photo :user="$provider" class="provider-avatar-photo" />
        <span class="provider-status {{ $profile->is_working || !$profile->is_available ? 'is-unavailable' : 'is-available' }}"><span aria-hidden="true"></span>{{ $profile->is_working ? 'Currently working' : ($profile->is_available ? 'Available for enquiries' : 'Currently unavailable') }}</span>
    </div>
    <div class="provider-identity"><h2>{{ $provider->name }}</h2><p>{{ $category->name }} professional</p></div>
    <a class="provider-rating" href="{{ route('providers.reviews', $provider) }}"><span class="rating-star" aria-hidden="true">★</span>@if($provider->reviews_count)<strong>{{ number_format($provider->reviews_avg_rating, 1) }} / 5</strong><span>({{ $provider->reviews_count }} {{ $provider->reviews_count === 1 ? 'review' : 'reviews' }})</span>@else<span>No reviews yet · Rate this provider</span>@endif<span aria-hidden="true">↗</span></a>
    <dl class="provider-facts">
        <div><dt>Service area</dt><dd>{{ $profile->service_area }}</dd></div>
        <div><dt>Experience</dt><dd>@if($profile->experience_years !== null){{ $profile->experience_years }} {{ $profile->experience_years === 1 ? 'year' : 'years' }}@else Not provided @endif</dd></div>
    </dl>
    @if($profile->biography)
    <div class="provider-about"><h3>About this professional</h3><p>{{ $profile->biography }}</p></div>
    @endif
    @if($profile->working_hours)
    <div class="provider-hours"><span>Working hours</span><strong>{{ $profile->working_hours }}</strong></div>
    @endif
    <div class="provider-contact">
        <p class="provider-status-note">Status set by provider. {{ $profile->is_working ? 'Busy on a job — ask about their next available time.' : ($profile->is_available ? 'Contact them to discuss your service needs.' : 'Ask about future availability before arranging a visit.') }}</p>
        @php($contactNumber = $profile->contactNumber())
        @if($contactNumber)
        <div class="provider-contact-actions">
            <a class="button" href="tel:+{{ $contactNumber }}" aria-label="Call {{ $provider->name }}">Call provider <span aria-hidden="true">↗</span></a>
            <a class="button secondary provider-whatsapp" href="https://wa.me/{{ $contactNumber }}?text={{ rawurlencode('Hi '.$provider->name.', I found your '.$category->name.' service on HomeServices. Are you available to help in '.$profile->service_area.'?') }}" target="_blank" rel="noopener noreferrer" aria-label="Message {{ $provider->name }} on WhatsApp (opens a new tab)">WhatsApp <span aria-hidden="true">↗</span></a>
        </div>
        <p class="provider-phone">{{ $profile->phone }}</p>
        @else
        <p class="provider-phone">Contact number needs an international country code.</p>
        @endif
    </div>
</article>
@empty
<div class="panel"><h2>No providers yet</h2><p>No professionals are registered for this category yet. Please check back soon.</p></div>
@endforelse
</div><div class="section">{{ $providers->links() }}</div>
@endsection
