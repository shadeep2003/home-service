@extends('layouts.app')
@section('title', 'Provider dashboard')
@section('content')
<section class="dashboard-header"><p class="eyebrow">PROVIDER WORKSPACE</p><h1>Your professional profile, {{ $provider->name }}.</h1><a class="button" href="{{ route('provider.profile.edit') }}">Edit profile</a></section>
@if(session('status'))<p class="profile-save-notice" role="status">{{ session('status') }}</p>@endif
<section class="profile-overview" aria-labelledby="profile-overview-title">
    <header class="profile-overview-header">
        <div class="profile-overview-heading"><x-profile-photo :user="$provider" class="profile-overview-photo" /><div><p class="eyebrow">YOUR PROFESSIONAL DETAILS</p><h2 id="profile-overview-title">Profile information</h2><p class="profile-overview-name">{{ $provider->name }}</p></div></div>
        @if($provider->providerProfile)
        @php($profile = $provider->providerProfile)
        <div class="profile-overview-status"><span class="provider-status {{ $profile->is_working || !$profile->is_available ? 'is-unavailable' : 'is-available' }}"><span aria-hidden="true"></span>{{ $profile->is_working ? 'Currently working on a job' : ($profile->is_available ? 'Available' : 'Currently unavailable') }}</span><a href="{{ route('provider.profile.edit') }}">Update your status <span aria-hidden="true">↗</span></a></div>
        @endif
    </header>
    <div class="profile-overview-body">
        @if($provider->providerProfile)
        <dl class="profile-detail-grid">
            <div class="profile-detail"><span class="profile-detail-icon" aria-hidden="true">☎</span><div><dt>Phone number</dt><dd>{{ $profile->phone }}</dd></div></div>
            <div class="profile-detail"><span class="profile-detail-icon" aria-hidden="true">⌖</span><div><dt>Service location</dt><dd>{{ $profile->service_area }}</dd></div></div>
            <div class="profile-detail"><span class="profile-detail-icon" aria-hidden="true">✦</span><div><dt>Experience</dt><dd>{{ $profile->experience_years !== null ? $profile->experience_years.' '.($profile->experience_years === 1 ? 'year' : 'years') : 'Not provided' }}</dd></div></div>
            <div class="profile-detail"><span class="profile-detail-icon" aria-hidden="true">◷</span><div><dt>Working hours</dt><dd>{{ $profile->working_hours ?: 'Not provided' }}</dd></div></div>
        </dl>
        @if($profile->biography)<div class="profile-overview-bio"><h3>About you</h3><p>{{ $profile->biography }}</p></div>@endif
        @else
        <p class="profile-overview-empty">Complete your profile and select your services to appear in the provider directory.</p>
        @endif
        <div class="profile-overview-services"><div><h3>Your service categories</h3><p>The services listed on your professional profile.</p></div><div class="profile-category-tags">
        @forelse ($provider->serviceCategories as $category)
        <span class="profile-category-tag {{ $category->is_active ? '' : 'is-inactive' }}"><span aria-hidden="true">{{ $category->is_active ? '✓' : '–' }}</span>{{ $category->name }}{{ $category->is_active ? '' : ' (inactive)' }}</span>
        @empty<p>No categories selected yet.</p>@endforelse
        </div></div>
    </div>
</section>
<x-profile-photo-upload :user="$provider" />
@endsection
