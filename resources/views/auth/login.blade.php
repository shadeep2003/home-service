@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<section class="auth-card"><p class="eyebrow">WELCOME BACK</p><h1>Your home. Your people.</h1>
<p class="muted">Log in to your Home Services account.</p>
@if(session('status'))<p role="status">{{ session('status') }}</p>@endif
<form method="POST" action="{{ route('login') }}">@csrf
<x-input name="email" label="Email address" type="email" autocomplete="email" required maxlength="255" />
<x-input name="password" label="Password" type="password" autocomplete="current-password" required />
<button class="button full" type="submit">Log in</button>
</form><p>New here? <a href="{{ route('register') }}">Create an account</a></p></section>
@endsection
