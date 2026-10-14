@props(['title', 'description' => null, 'image', 'imageAlt', 'imageSrcset' => null, 'url', 'action' => 'View providers', 'label' => 'HOME SERVICES'])
<article {{ $attributes->class(['service-card', 'marketplace-service-card']) }}>
    <div class="service-card__media">
        <img class="service-card__image" src="{{ $image }}" @if($imageSrcset) srcset="{{ $imageSrcset }}" sizes="(max-width: 640px) calc(100vw - 40px), (max-width: 1024px) calc((100vw - 80px) / 2), 360px" @endif width="960" height="540" alt="{{ $imageAlt }}" loading="lazy" decoding="async">
        <span class="service-card__label">{{ $label }}</span>
    </div>
    <div class="service-card__body">
        <h3 class="service-card__title">{{ $title }}</h3>
        <p class="service-card__description">{{ $description ?: 'Find local professionals and discuss the right service for your home.' }}</p>
        <a class="service-card__button" href="{{ $url }}" aria-label="{{ $action }} for {{ $title }}"><span>{{ $action }}</span><span class="service-card__arrow" aria-hidden="true">↗</span></a>
    </div>
</article>
