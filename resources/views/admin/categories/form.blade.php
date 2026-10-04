@extends('layouts.app')
@section('title', $category->exists ? 'Edit category' : 'Add category')
@section('content')
<section class="auth-card"><h1>{{ $category->exists ? 'Edit category' : 'Add category' }}</h1>
<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">@csrf @if($category->exists) @method('PUT') @endif
@foreach(['name' => 'Name', 'slug' => 'URL slug', 'description' => 'Description'] as $field => $label)
<div class="field"><label for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $category->$field) }}" maxlength="{{ $field === 'description' ? 1500 : ($field === 'slug' ? 120 : 100) }}" @required($field !== 'description')>
@error($field)<p class="field-error">{{ $message }}</p>@enderror</div>@endforeach
<p class="muted">Use a lowercase URL slug, for example: ac-repair.</p>
<div class="field"><label for="icon">Icon</label><select id="icon" name="icon">
@foreach(['check' => 'General', 'Electrical' => 'Electrical', 'Plumbing' => 'Plumbing', 'Cleaning' => 'Cleaning', 'Painting' => 'Painting', 'AC Repair' => 'AC Repair', 'Gardening' => 'Gardening'] as $value => $label)
<option value="{{ $value }}" @selected(old('icon', $category->icon ?? 'check') === $value)>{{ $label }}</option>@endforeach</select>@error('icon')<p class="field-error">{{ $message }}</p>@enderror</div>
<div class="field"><label for="is_active">Status</label><select id="is_active" name="is_active"><option value="1" @selected(old('is_active', $category->is_active))>Active</option><option value="0" @selected(!old('is_active', $category->is_active))>Inactive</option></select>@error('is_active')<p class="field-error">{{ $message }}</p>@enderror</div>
<button class="button full">Save category</button></form><p><a href="{{ route('admin.categories.index') }}">Back to categories</a></p></section>
@endsection
