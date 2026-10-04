@props(['categories'])
<div class="service-grid">
@forelse ($categories as $category)
<x-service-card :category="$category" />
@empty
<div class="panel"><h3>Services are coming soon</h3><p>No active service categories are available yet. Please check back soon.</p></div>
@endforelse
</div>
