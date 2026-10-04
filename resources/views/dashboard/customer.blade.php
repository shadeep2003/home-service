@extends('layouts.app')
@section('title', 'Customer dashboard')
@section('content')
<section class="dashboard-header"><p class="eyebrow">CUSTOMER WORKSPACE</p><h1>Welcome home, {{ auth()->user()->name }}.</h1><a class="button secondary" href="{{ route('profile.edit') }}">Edit profile</a><p class="lead">Your bookings and upcoming services will appear here once the booking module is implemented.</p></section>
<div class="panel"><h2>Find professional help</h2><a class="button" href="{{ route('services') }}">Browse services</a><p>Your customer account has access to this workspace. Additional modules are planned, and no booking statistics are available yet.</p></div>

@endsection
