@props(['href', 'variant' => 'primary'])
<a href="{{ $href }}" {{ $attributes->class(['button', 'secondary' => $variant === 'secondary']) }}>{{ $slot }}</a>
