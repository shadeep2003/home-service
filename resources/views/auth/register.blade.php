@extends('layouts.app')
@section('title', 'Create an account')
@section('content')
<section class="auth-card"><p class="eyebrow">MAKE YOURSELF AT HOME</p><h1>A little help starts here.</h1>
<p class="muted">Create your account as a customer or service provider.</p>
<form method="POST" action="{{ route('register') }}">@csrf
<x-input name="name" label="Full name" autocomplete="name" required maxlength="100" />
<x-input name="email" label="Email address" type="email" autocomplete="email" required maxlength="255" />
<div class="field"><label for="role">I want to</label><select id="role" name="role" required @error('role') aria-invalid="true" aria-describedby="role-error" @enderror>
<option value="customer" @selected(old('role') === 'customer')>Find help for my home</option>
<option value="provider" @selected(old('role', request('role')) === 'provider')>Offer professional services</option></select>
@error('role')<p class="field-error" id="role-error">{{ $message }}</p>@enderror</div>
<div id="provider-fields"><x-provider-fields :categories="$categories" /></div>
<p class="muted">Use at least 8 characters, including letters and numbers.</p>
<x-input name="password" label="Password" type="password" autocomplete="new-password" required minlength="8" />
<x-input name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required />
<button class="button full" type="submit">Create account</button>
</form><p>Already registered? <a href="{{ route('login') }}">Log in</a></p></section>
<script src="{{ asset('js/provider-registration.js') }}" defer></script>
@endsection
