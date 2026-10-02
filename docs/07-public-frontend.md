# Public frontend foundation

This phase serves Blade pages without querying models or creating records. No migrations were run. Service categories, professional profiles and testimonials are explicitly labelled static demonstrations. The contact page provides guidance and FAQs without a message submission form or invented contact details.

## Run locally

From the project directory:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Visit http://127.0.0.1:8000/ in your browser. Other pages are `/services`, `/about`, `/contact`, `/login` and `/register`. How It Works links to `/#how-it-works`. No npm install, Vite build or database migration is required for the public UI. CSS, JavaScript and illustrations are local. Existing login/registration submissions still require the authentication database configured by the earlier phase.

## Files changed

| File | Purpose |
| --- | --- |
| `routes/web.php` | Public named GET routes; preserves authentication and role routes. |
| `app/Http/Controllers/PublicPageController.php` | Returns four public views; no models or SQL. |
| `resources/views/layouts/app.blade.php` | Shared document shell, metadata, CSS, deferred navigation script, navbar, main and footer. |
| `resources/views/welcome.blade.php` | New homepage with all nine requested areas. |
| `resources/views/public/services.blade.php` | Category preview catalogue and planned workflow. |
| `resources/views/public/about.blade.php` | Purpose, project status and guiding values. |
| `resources/views/public/contact.blade.php` | Householder/provider entry points and expandable FAQs. |
| `resources/views/components/navbar.blade.php` | Shared public links, active page state, mobile Menu, existing auth-aware actions. |
| `resources/views/components/footer.blade.php` | Shared multi-column navigation, account links and project status. |
| `resources/views/components/button.blade.php` | Link button with primary/secondary variants and attribute merging. |
| `resources/views/components/section-heading.blade.php` | Eyebrow, heading and optional supporting text. |
| `resources/views/components/icon.blade.php` | Reusable decorative inline SVG service icons. |
| `resources/views/components/service-card.blade.php` | Category name, icon, description and preview label. |
| `resources/views/components/service-grid.blade.php` | Six static category previews shared by homepage and Services. |
| `resources/views/components/home-illustration.blade.php` | Local SVG house with CSS scenery and floating tools. |
| `resources/views/components/how-it-works.blade.php` | Shared four-step planned workflow with homepage anchor. |
| `resources/views/components/cta.blade.php` | Shared service discovery call to action. |
| `public/css/app.css` | Shared design tokens, component styling, responsive layouts, focus and reduced-motion styles; retains auth/dashboard styling. |
| `public/js/navigation.js` | Opens desktop navigation, collapses mobile navigation, and responds to viewport changes. |
| `tests/Feature/PublicPagesTest.php` | Database-independent HTTP checks, demo disclosure and provider signup selection. |
| `docs/05-design-system.md` | Updated visual foundation documentation. |
| `docs/07-public-frontend.md` | Implementation, run instructions, file inventory and viva notes. |

Laravel also generates runtime compiled views during rendering; these are not application source. Authentication controller, requests, models, forms and database schema were not modified.

## Reuse and request flow

Pages use `@extends('layouts.app')` and fill `@section('title')` and `@section('content')`. The layout renders navbar/footer exactly once. Anonymous Blade components are invoked with `<x-service-card>` etc. `@props` declares component inputs; `$slot` supplies button content; `$attributes->class()` merges caller classes. Normal Blade interpolation escapes text.

`GET /` → named `home` route → `PublicPageController::home()` → `welcome` view → shared layout/components → HTML response. Services, About and Contact follow the same flow. Public routes are outside the `auth` middleware group; Laravel's normal web middleware remains. Authentication GET routes still render their original forms and POST routes still use `AuthController`. Provider CTA supplies `role=provider` to the existing registration view.

## Design and responsiveness

Navy `#123047`, teal `#087f82`, orange `#f4a361`, off-white `#f8f9f6`, white cards, 18px card radius and subtle navy shadow are CSS custom properties. System typography and inline SVG avoid external asset dependencies. Grid layouts adapt at 980px and 700px with an extra narrow-screen adjustment at 380px. At 981px and above navigation is expanded; below it native details/summary supports keyboard activation. With JavaScript disabled the menu starts expanded so every link stays accessible.

Accessibility provisions include semantic navigation, skip link, active-page `aria-current`, focus ring, labelled illustration, decorative icon hiding, native disclosure controls and reduced-motion preferences. Browser visual validation and a full accessibility audit remain outstanding; no conformance claim is made.

## Verification

```bash
vendor/bin/phpunit tests/Feature/PublicPagesTest.php
php artisan route:list --except-vendor
php artisan view:cache
php artisan view:clear
node --check public/js/navigation.js
find app routes resources/views -name '*.php' -print0 | xargs -0 -n1 php -l
```

Result: 3 tests, 62 assertions passed. Public and auth GET pages render even with a deliberately unavailable database connection. PHP syntax and compiled Blade syntax passed, as did JavaScript syntax. The existing database-dependent authentication suite was not run because it uses RefreshDatabase (migrations). This execution environment blocked local server sockets, so browser rendering could not be visually checked.

## Viva points

- Blade is server-side rendering: Laravel produces HTML before the browser receives it.
- Routes map URLs to controller methods; thin public controllers return views without business or persistence logic.
- Layout inheritance centralizes the document shell; components centralize reusable UI fragments and their inputs.
- Named routes avoid hardcoded application URLs and preserve links if paths change later.
- CSS custom properties form the reusable design system; media queries and grids provide responsive layouts.
- Static UI arrays are presentation fixtures, not models, seeders or fake database records. Future catalogue data can replace them while retaining card markup.
- Public pages are outside auth middleware. Existing protected dashboards and authentication POST flows remain intact.
- No contact submission or booking action is simulated; previews explicitly distinguish planned features from working navigation.
- Database-independent HTTP tests verify the foundation in isolation; transactional authentication testing belongs to the database-enabled phase.
