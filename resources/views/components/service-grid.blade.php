@props(['categories'])
<div class="service-grid services-grid">
@forelse ($categories as $category)
<x-service-card :title="$category->name" :description="$category->description" :image="$category->imageUrl()" :image-alt="$category->imageAlt()" :image-srcset="$category->imageSrcset()" :url="route('services.category', $category)" action="Explore services" label="SERVICE CATEGORY" />
@empty
<x-service-empty-state title="Services are coming soon" description="No active service categories are available yet. Please check back soon." :url="route('home')" action="Back to home" />
@endforelse
</div>
