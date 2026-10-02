@extends('layouts.app')
@section('title', 'Customer dashboard')
@section('content')
<section class="dashboard-header"><p class="eyebrow">CUSTOMER WORKSPACE</p><h1>Welcome home, {{ auth()->user()->name }}.</h1><p class="lead">Your bookings and upcoming services will appear here once the booking module is implemented.</p></section>
<div class="panel"><h2>Account access is ready</h2><p>Your customer account has access to this workspace. Additional modules are planned, and no booking statistics are available yet.</p></div>
@endsection
