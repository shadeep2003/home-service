@props(['name'])
<svg {{ $attributes }} width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('Electrical')<path d="m13 2-9 12h7l-1 8 10-13h-7z"/>@break
@case('Plumbing')<path d="M6 3v8a4 4 0 0 0 4 4h5v6M3 3h6M12 21h6M14 5h7v6h-7zM17 2v3"/>@break
@case('Cleaning')<path d="m15 2-6 12M7 12l6 3-3 7-8-4zM18 9v6m-3-3h6"/>@break
@case('Painting')<rect x="3" y="3" width="14" height="6" rx="2"/><path d="M17 6h4v7h-9v3m-2 0h4v6h-4z"/>@break
@case('AC Repair')<path d="M12 2v20M3 7l18 10M3 17 21 7M9 4l3 3 3-3M9 20l3-3 3 3M3 11l4-1-1-4M18 18l-1-4 4-1M3 13l4 1-1 4M18 6l-1 4 4 1"/>@break
@case('Gardening')<path d="M12 22V12M12 16C3 17 2 9 3 5c6 0 10 3 9 11zM12 12c0-7 4-10 9-10 1 6-3 10-9 10z"/>@break
@case('dashboard')<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>@break
@case('services')<path d="M14 6a5 5 0 0 0-6 6l-5 5a2 2 0 0 0 3 3l5-5a5 5 0 0 0 6-6l-3 3-3-3z"/>@break
@case('categories')<rect x="3" y="4" width="18" height="5" rx="1.5"/><rect x="3" y="14" width="8" height="6" rx="1.5"/><rect x="15" y="14" width="6" height="6" rx="1.5"/>@break
@case('users')<circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6M21 21v-2a6 6 0 0 0-4-5"/>@break
@case('provider')<circle cx="12" cy="6" r="3"/><path d="M6 21v-4a6 6 0 0 1 12 0v4M6 17h12M10 17v4M14 17v4"/>@break
@case('profile')<circle cx="12" cy="12" r="9"/><circle cx="12" cy="9" r="3"/><path d="M5 18a7 7 0 0 1 14 0"/>@break
@case('logout')<path d="M9 3H4v18h5M9 12h12M17 8l4 4-4 4"/>@break
@case('sidebar')<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16M14 9l-3 3 3 3"/>@break
@case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
@case('close')<path d="m6 6 12 12M6 18 18 6"/>@break
@case('external')<path d="M14 3h7v7M21 3 10 14M10 3H4a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-6"/>@break
@case('chevron-right')<path d="m9 5 7 7-7 7"/>@break
@default<path d="m5 12 4 4L19 6"/><circle cx="12" cy="12" r="10"/>
@endswitch
</svg>
