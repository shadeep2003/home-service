@extends('layouts.app')
@section('title', 'Provider dashboard')
@section('content')
<section class="dashboard-header"><p class="eyebrow">PROVIDER WORKSPACE</p><h1>Your next job starts here, {{ auth()->user()->name }}.</h1><p class="lead">Booking requests, availability, and your professional profile will be added in the provider phase.</p></section>
<div class="panel"><h2>Account access is ready</h2><p>Your provider account has access to this workspace. Additional modules are planned, and no booking statistics are available yet.</p></div>
@endsection
