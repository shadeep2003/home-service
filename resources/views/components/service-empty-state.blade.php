@props(['title', 'description', 'url', 'action'])
<div class="service-empty-state">
    <span class="service-empty-state__icon" aria-hidden="true"><x-icon name="check" /></span>
    <h2>{{ $title }}</h2><p>{{ $description }}</p>
    <a class="button secondary" href="{{ $url }}">{{ $action }} <span aria-hidden="true">→</span></a>
</div>
