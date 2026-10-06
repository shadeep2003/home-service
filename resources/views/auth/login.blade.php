@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<x-auth-shell mode="login">
<section class="auth-form"><p class="eyebrow">WELCOME BACK</p><h1>Welcome home.</h1>
<p class="muted">A little less to do. A little more time for you.</p>
@if(session('status'))<p role="status">{{ session('status') }}</p>@endif
<form method="POST" action="{{ route('login') }}">@csrf
<x-input name="email" label="Email address" type="email" autocomplete="email" required maxlength="255" />
<x-input name="password" label="Password" type="password" autocomplete="current-password" required />
<button class="button full" type="submit">Log in</button>
</form><p>New here? <a href="{{ route('register') }}">Create an account</a></p></section>
</x-auth-shell>
@endsection
