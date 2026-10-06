@extends('layouts.app')
@section('title', 'Please wait')
@section('content')
<section class="auth-card"><p class="eyebrow">PLEASE WAIT</p><h1>Too many requests</h1>
<p role="alert">Please wait {{ $retryAfter }} seconds before trying again.</p>
<a class="button full" href="{{ $returnUrl }}">Return to login verification</a></section>
@endsection
