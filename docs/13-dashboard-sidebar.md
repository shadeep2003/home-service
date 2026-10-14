# Dashboard navigation

The authenticated workspace uses `resources/views/layouts/dashboard.blade.php`. Customer, provider and admin dashboards, profile editors, provider subcategory management and admin category forms extend this layout. Public pages continue using `layouts.app`, the existing public navbar and footer. No backend routes, authentication flows, middleware, account data or migrations were changed for this sidebar.

`App\Support\DashboardNavigation` builds role-specific groups from the authenticated user and current request. The sidebar, section and item components share the markup. Navigation is presentation only: the existing middleware remains the authority for access.

| Role | Navigation |
| --- | --- |
| Customer | Dashboard; Browse services; My profile |
| Service provider | Provider dashboard; My services; Browse marketplace; Edit provider profile |
| Administrator | Admin dashboard; User management; Service providers; Service categories; My profile |

User management points to the existing admin account table at `#accounts`. Service providers uses its existing `role=provider` filter. Route patterns select nested category/profile pages; the lightweight script handles the account-table fragment because URL fragments do not reach Laravel. Missing bookings, jobs and settings features are omitted.

The sidebar preserves the existing HomeServices logo, navy and teal palette and lightweight SVG icon system. Its user card uses `User::profilePhotoUrl()` or Unicode initials, with long-name truncation and an accessible full-name label. Logout remains a CSRF-protected POST to the existing route. The sidebar displays no email address and requires no additional queries.

Desktop and tablet widths are 264px expanded and 76px collapsed. The main content width follows the sidebar. The collapse preference is stored as a boolean under `homeservices.sidebar.collapsed`, read before CSS paints and handled safely when storage is unavailable. Collapsed links keep their SVG icons, accessible names and native title tooltips. Ordinary transitions last 200–220ms and are disabled for reduced-motion users.

Below 768px, navigation becomes a modal off-canvas drawer. It supports hamburger/close/backdrop controls, Escape, focus trapping and restoration, scroll locking, and inert background content. Closed navigation is inert and hidden from the accessibility tree. Resizing to desktop clears modal state. Without JavaScript, mobile navigation stays in the document flow rather than becoming inaccessible.

Assets are scoped to `public/css/dashboard.css` and `public/js/dashboard-navigation.js`. The existing `public/css/app.css` is reused for content. This repository has no package.json, Vite configuration or npm build pipeline, so no Vite build is applicable and no new frontend dependencies were introduced.

`DashboardNavigationTest` checks role visibility, all sidebar destination routes, nested active states, initials/photos, account-table destinations, private-field exclusion, no added queries, public-navbar separation, logout and existing authorization. Browser verification uses separate temporary SQLite accounts and sessions rather than the application's MySQL users.

Files created:

- `app/Support/DashboardNavigation.php`
- `resources/views/layouts/dashboard.blade.php`
- `resources/views/components/dashboard/sidebar.blade.php`
- `resources/views/components/dashboard/sidebar-item.blade.php`
- `resources/views/components/dashboard/sidebar-section.blade.php`
- `public/css/dashboard.css`
- `public/js/dashboard-navigation.js`
- `tests/Feature/DashboardNavigationTest.php`
- `docs/13-dashboard-sidebar.md`

Existing files updated for this sidebar:

- `resources/views/dashboard/customer.blade.php`
- `resources/views/dashboard/provider.blade.php`
- `resources/views/dashboard/admin.blade.php`
- `resources/views/profile/edit.blade.php`
- `resources/views/provider/edit.blade.php`
- `resources/views/provider/subcategories.blade.php`
- `resources/views/admin/categories/index.blade.php`
- `resources/views/admin/categories/form.blade.php`
- `resources/views/components/icon.blade.php`

Verification on 2026-10-15: the full Laravel suite passed with 91 tests and 1,125 assertions. The new feature class contributed 6 tests and 134 assertions. Isolated headless browser verification passed 48 interaction checks, covering all roles, real profile-photo serving, collapse persistence, main-content adjustment, nested active states, account filtering, mobile modal/focus/backdrop/Escape behavior, 320px overflow, logout, blocked storage and reduced motion. No browser JavaScript errors or missing page assets were reported. PHP/JavaScript syntax and Git whitespace checks passed. No Vite build exists to run.
