@props(['name', 'description'])
<article class="service-card"><div class="service-icon"><x-icon :name="$name" /></div><h3>{{ $name }}</h3><p>{{ $description }}</p><span class="card-note">Category preview <span aria-hidden="true">↗</span></span></article>
