@extends('layouts.app')
@section('title', 'Manage service categories')
@section('content')
<section class="dashboard-header"><p class="eyebrow">ADMIN WORKSPACE</p><h1>Service categories</h1><a class="button" href="{{ route('admin.categories.create') }}">Add category</a></section>
@if(session('status'))<p role="status">{{ session('status') }}</p>@endif
<div class="service-grid">@forelse($categories as $category)
<article class="service-card"><h3>{{ $category->name }}</h3><p>{{ $category->description }}</p><p>{{ $category->is_active ? 'Active' : 'Inactive' }}</p><a href="{{ route('admin.categories.edit', $category) }}">Edit / change activation</a></article>
@empty<div class="panel"><p>No categories yet. Add your first category.</p></div>@endforelse</div>
<div class="section">{{ $categories->links() }} <a href="{{ route('admin.dashboard') }}">Back to dashboard</a></div>
@endsection
