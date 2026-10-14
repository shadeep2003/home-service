@extends('layouts.app')
@section('title', $category->name.' services')
@section('content')
<section class="catalogue-header">
    <nav class="catalogue-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('services') }}">← All services</a><span aria-hidden="true">/</span><span aria-current="page">{{ $category->name }}</span></nav>
    <div class="catalogue-header__row"><div><p class="eyebrow"><span class="eyebrow-line" aria-hidden="true"></span>EXPLORE SERVICES</p><h1>{{ $category->name }}</h1><p class="lead">{{ $category->description ?: 'Find the right service for a home that feels its best.' }}</p><p class="catalogue-header__guide">Choose a subcategory to find local providers.</p></div><span class="catalogue-header__count">{{ $subcategories->total() }} {{ $subcategories->total() === 1 ? 'service to explore' : 'services to explore' }}</span></div>
</section>
<section class="catalogue-list" aria-label="{{ $category->name }} subcategories">
<div @class(['service-grid', 'services-grid', 'services-grid--single' => $subcategories->count() === 1, 'services-grid--pair' => $subcategories->count() === 2])>
@forelse($subcategories as $subcategory)
<x-service-card :title="$subcategory->name" :description="$subcategory->description" :image="$subcategory->imageUrl()" :image-alt="$subcategory->imageAlt()" :image-srcset="$subcategory->imageSrcset()" :url="route('services.subcategory', [$category, $subcategory])" :label="$category->name" />
@empty
<x-service-empty-state title="No subcategories yet" description="Providers have not added services here yet. Explore another category or check back soon." :url="route('services')" action="Browse other services" />
@endforelse
</div>
<div class="catalogue-pagination">{{ $subcategories->links() }}</div>
</section>
@endsection
