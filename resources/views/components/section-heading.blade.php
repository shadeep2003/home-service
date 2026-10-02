@props(['eyebrow', 'title', 'description' => null])
<div {{ $attributes->class(['section-heading']) }}><p class="eyebrow">{{ $eyebrow }}</p><h2>{{ $title }}</h2>@if($description)<p class="lead">{{ $description }}</p>@endif</div>
