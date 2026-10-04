@props(['category'])
@php($theme = in_array($category->slug, ['electrical', 'plumbing', 'cleaning', 'painting', 'ac-repair', 'gardening'], true) ? $category->slug : 'general')
<article class="service-card service-category-card service-theme-{{ $theme }}">
    <div class="service-category-art" aria-hidden="true"><div class="service-icon"><x-icon :name="$category->icon ?? 'check'" /></div><span class="service-category-watermark"><x-icon :name="$category->icon ?? 'check'" /></span></div>
    <div class="service-category-content"><h3>{{ $category->name }}</h3><p>{{ $category->description }}</p></div>
    <a class="card-note service-category-link" href="{{ route('services.category', $category->slug) }}" aria-label="View {{ $category->name }} providers"><span>View providers</span><span class="service-category-arrow" aria-hidden="true">↗</span></a>
</article>
