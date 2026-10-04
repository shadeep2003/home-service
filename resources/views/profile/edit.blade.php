@extends('layouts.app')
@section('title', 'Edit profile')
@section('content')
<section class="profile-editor"><header class="profile-editor-header"><a href="{{ route('dashboard') }}">← Back to dashboard</a><p class="eyebrow">YOUR ACCOUNT</p><h1>Edit your profile</h1><p>Manage your personal details and profile picture in one place.</p></header><div class="profile-editor-body">
@if(session('status'))<p class="profile-save-notice" role="status">{{ session('status') }}</p>@endif
<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">@csrf @method('PUT')<x-personal-fields :user="$user" /><div class="profile-editor-actions"><p>Save all your changes together.</p><div><a class="button secondary" href="{{ route('dashboard') }}">Cancel</a><button class="button">Save profile ✓</button></div></div></form></div></section>
@endsection
