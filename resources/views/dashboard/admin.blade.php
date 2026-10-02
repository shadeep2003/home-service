@extends('layouts.app')
@section('title', 'Admin dashboard')
@section('content')
<section class="dashboard-header"><p class="eyebrow">ADMIN WORKSPACE</p><h1>Platform overview, {{ auth()->user()->name }}.</h1><p class="lead">User management, complaints, and system statistics will be added in the administration phase.</p></section>
<div class="panel"><h2>Account access is ready</h2><p>Your admin account has access to this workspace. Additional modules are planned, and no booking statistics are available yet.</p></div>
@endsection
