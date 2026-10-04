@extends('layouts.app')
@section('title', 'Edit professional profile')
@section('content')
<section class="profile-editor">
<header class="profile-editor-header"><a href="{{ route('provider.dashboard') }}">← Back to dashboard</a><p class="eyebrow">YOUR PROFESSIONAL PROFILE</p><h1>Edit professional profile</h1><p>Help customers get to know you. Keep your services, contact details and availability up to date.</p></header>
<div class="profile-editor-body">
<x-profile-photo-upload :user="$provider" />
<form method="POST" action="{{ route('provider.profile.update') }}">@csrf @method('PUT')
<x-provider-fields :categories="$categories" :profile="$provider->providerProfile" :selected="$provider->serviceCategories->modelKeys()" />
<section class="profile-editor-status" aria-labelledby="status-heading"><div class="profile-editor-section-heading"><span aria-hidden="true">02</span><div><h2 id="status-heading">Availability & work status</h2><p>Let customers know when you can help.</p></div></div><div class="profile-editor-status-grid">
<div class="field"><label for="is_available">Availability</label><select id="is_available" name="is_available"><option value="1" @selected(old('is_available', $provider->providerProfile?->is_available ?? true))>Available</option><option value="0" @selected(!old('is_available', $provider->providerProfile?->is_available ?? true))>Currently unavailable</option></select>@error('is_available')<p class="field-error">{{ $message }}</p>@enderror</div>
<div class="field"><label for="is_working">Current work status</label><select id="is_working" name="is_working"><option value="0" @selected(!old('is_working', $provider->providerProfile?->is_working ?? false))>Not currently working on a job</option><option value="1" @selected(old('is_working', $provider->providerProfile?->is_working ?? false))>Currently working on a job</option></select><p class="muted">Update this when you start or finish a job. Customers will see this status on your card.</p>@error('is_working')<p class="field-error">{{ $message }}</p>@enderror</div>
</div></section>
<div class="profile-editor-actions"><p>Your changes appear on your public provider card.</p><div><a class="button secondary" href="{{ route('provider.dashboard') }}">Cancel</a><button class="button" type="submit">Save profile <span aria-hidden="true">✓</span></button></div></div></form></div></section>
@endsection
