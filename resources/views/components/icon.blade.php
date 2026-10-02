@props(['name'])
<svg {{ $attributes }} width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('Electrical')<path d="m13 2-9 12h7l-1 8 10-13h-7z"/>@break
@case('Plumbing')<path d="M6 3v8a4 4 0 0 0 4 4h5v6M3 3h6M12 21h6M14 5h7v6h-7zM17 2v3"/>@break
@case('Cleaning')<path d="m15 2-6 12M7 12l6 3-3 7-8-4zM18 9v6m-3-3h6"/>@break
@case('Painting')<rect x="3" y="3" width="14" height="6" rx="2"/><path d="M17 6h4v7h-9v3m-2 0h4v6h-4z"/>@break
@case('AC Repair')<path d="M12 2v20M3 7l18 10M3 17 21 7M9 4l3 3 3-3M9 20l3-3 3 3M3 11l4-1-1-4M18 18l-1-4 4-1M3 13l4 1-1 4M18 6l-1 4 4 1"/>@break
@case('Gardening')<path d="M12 22V12M12 16C3 17 2 9 3 5c6 0 10 3 9 11zM12 12c0-7 4-10 9-10 1 6-3 10-9 10z"/>@break
@default<path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="10"/>
@endswitch
</svg>
