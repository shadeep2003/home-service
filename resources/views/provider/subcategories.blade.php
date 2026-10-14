@extends('layouts.dashboard')
@section('title', 'Manage subcategories')
@section('content')
<section class="page-intro"><a href="{{ route('provider.dashboard') }}">← Provider dashboard</a><h1>Your service subcategories</h1><p>Create specific services under an active category. Category names are managed by admins.</p></section>
@if(session('status'))<p class="profile-save-notice" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="panel" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<section class="panel subcategory-management"><h2>Add a subcategory</h2>
@if($categories->isEmpty())<p>No active categories are available. Check back after an admin activates one.</p>@else
<form method="post" action="{{ route('provider.subcategories.store') }}">@csrf
<label for="parent">Category</label><select id="parent" name="service_category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('service_category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select>
<label for="name">Service name</label><input id="name" name="name" value="{{ old('name') }}" maxlength="100" required>
<label for="description">Description</label><textarea id="description" name="description" maxlength="1500">{{ old('description') }}</textarea><button class="button">Add subcategory</button>
</form>@endif</section>
<div class="service-grid section">
@forelse($subcategories as $subcategory)
<article class="panel subcategory-management"><h2>{{ $subcategory->category->name }} → {{ $subcategory->name }}</h2>
@if($subcategory->category->is_active)
<form method="post" action="{{ route('provider.subcategories.update', $subcategory) }}">@csrf @method('PUT')
<input type="hidden" name="service_category_id" value="{{ $subcategory->service_category_id }}">
<label for="name-{{ $subcategory->id }}">Service name</label><input id="name-{{ $subcategory->id }}" name="name" value="{{ $subcategory->name }}" maxlength="100" required>
<label for="description-{{ $subcategory->id }}">Description</label><textarea id="description-{{ $subcategory->id }}" name="description" maxlength="1500">{{ $subcategory->description }}</textarea><button class="button">Save changes</button></form>
@else<p>This parent category is inactive. Editing will be available when an admin activates it.</p>@endif
<form method="post" action="{{ route('provider.subcategories.destroy', $subcategory) }}">@csrf @method('DELETE')<button class="button secondary">Remove subcategory</button></form></article>
@empty<div class="panel"><h2>No custom subcategories yet</h2><p>Add your specific services above. Your existing category selections remain listed under General services.</p></div>@endforelse
</div>
@endsection
