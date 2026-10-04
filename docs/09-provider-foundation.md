# Service categories and provider foundation — implementation / viva report

1. **කලින් තිබුණේ:** Laravel 13 authentication, User/Role enum, role middleware, users migration, Blade components සහ dashboard shells. Categories hardcoded වුණා. Database document එකේ profiles සහ many-to-many provider services සැලසුම් කරලා තිබුණා.
2. **වෙනස් කළේ:** ඒ architecture එක extend කළා. Real categories, provider profile/category registration, public provider directory, profile edit සහ small Admin category management තියෙනවා. Existing colors/components preserve කළා. Homepage එක real categories හයක් දක්වා පෙන්වනවා; demo professionals/testimonials ඉවත් කළා. Booking තවම planned.
3. **Migration:** `2026_10_02_000002_create_service_provider_foundation.php` අලුත් tables තුනක් create කරනවා: service_categories, provider_profiles, provider_services. Existing users migration වෙනස් කළේ නැහැ. Seeders default categories හය insert කරනවා; repeat run එකෙන් Admin edits overwrite වෙන්නේ නැහැ.
4. **Relationships:** User → ProviderProfile one-to-one (`user_id` unique). User ↔ ServiceCategory many-to-many through provider_services (`provider_id` references users). Pivot pair unique. User deletion cascades profile/links; linked category deletion restricted. Admin deactivation links preserve කරනවා.
5. **Models:** User එකට providerProfile/serviceCategories relationships එකතු කළා. New ProviderProfile සහ ServiceCategory models වල fillable/casts/relationships තියෙනවා. Separate Provider model එකක් අවශ්‍ය නැහැ.
6. **Controllers/routes:** AuthController GET /register categories load කරනවා; POST /register existing flow extend කරනවා. PublicPageController GET / සහ /services active categories ලබාදෙනවා; GET /services/{category:slug} provider directory. ProviderProfileController protected GET /provider/dashboard, GET /provider/profile/edit, PUT /provider/profile. ServiceCategoryController Admin index/create/store/edit/update routes under /admin/categories. Existing role middleware routes protect කරනවා.
7. **Provider registration:** Provider role තෝරන විට professional fields පෙන්වනවා. Phone/service area/categories required; bio/experience/working hours optional. Categories database එකෙන් load වෙනවා. Form Request validated data → transaction → User save → profile create → attach category IDs → commit → login/session regeneration → dashboard. Customer request එකේ provider fields exclude කරන නිසා crafted payload එකකින් provider records හැදෙන්නේ නැහැ. Existing provider accounts Edit Profile එකෙන් profile complete කළ හැකියි.
8. **Category filtering:** Slug route model binding category resolve කරනවා. Inactive/missing category 404. `$category->providers()` pivot relationship එකෙන් matching provider-role users පමණක් retrieve කරනවා; profile තිබිය යුතුයි. Eager loading profile සඳහා N+1 queries අඩු කරනවා. Pagination page එකකට providers 12. Name/location/bio/experience/availability real records වලින් පමණක්. No providers නම් empty state.
9. **Validation/security:** Public roles customer/provider පමණයි; Admin rejected. IDs integer/distinct/existing/active විය යුතුයි. Password hashed cast සහ confirmation/password rules preserve කළා. CSRF, role/auth middleware, session rotation, auth throttling, escaped Blade output, fillable protection සහ FK/unique constraints තියෙනවා. Profile update current authenticated user පමණක් target කරනවා. Admin names/slugs unique; slug format validated; icon identifier allowlisted. No deletion route.
10. **Tests:** Existing AuthenticationTest/PublicPagesTest update කළා; ServiceProviderFoundationTest එකතු කළා. 24 tests / 183 assertions passed on isolated SQLite. Invalid input saves no accounts; invalid edits preserve profile. MySQL and visual browser checks localව කරන්න.
11. **Safe commands:** Existing configured project එකේ පහත commands run කරන්න. Existing data delete නොකරයි; default seed records missing නම් insert කරනවා.

```bash
composer dump-autoload
php artisan migrate
php artisan db:seed --class=ServiceCategorySeeder
php artisan test
php artisan serve
```

`php artisan db:seed` ද DatabaseSeeder හරහා එම category seeder run කරනවා. `migrate:fresh` අවශ්‍ය නැහැ. මේ implementation අතරතුර application MySQL data migrate/seed කළේ නැහැ; tests SQLite isolated database භාවිතා කළා.

12. **Files changed:**

- Existing backend: `app/Models/User.php`, `app/Http/Controllers/{AuthController,PublicPageController}.php`, `app/Http/Requests/RegisterRequest.php`, `routes/web.php`, `composer.json` (Seeder PSR-4 autoload only).
- New backend: `app/Models/{ServiceCategory,ProviderProfile}.php`, `app/Http/Controllers/{ProviderProfileController,ServiceCategoryController}.php`, `app/Http/Requests/{ProviderProfileRequest,ServiceCategoryRequest}.php`.
- Database: new foundation migration, `database/seeders/{DatabaseSeeder,ServiceCategorySeeder}.php`.
- Existing UI: `resources/views/welcome.blade.php`, `resources/views/public/services.blade.php`, `resources/views/auth/register.blade.php`, `resources/views/dashboard/{customer,provider,admin}.blade.php`, `resources/views/components/{service-grid,service-card,footer}.blade.php`, `public/css/app.css`.
- New UI: `resources/views/components/provider-fields.blade.php`, `resources/views/public/category.blade.php`, `resources/views/provider/edit.blade.php`, `resources/views/admin/categories/{index,form}.blade.php`, `public/js/provider-registration.js`.
- Tests: `tests/Feature/{AuthenticationTest,PublicPagesTest,ServiceProviderFoundationTest}.php`.
- Documentation: README, docs/02-architecture.md, docs/03-database-design.md, docs/04-authentication.md, docs/06-test-plan.md, docs/07-public-frontend.md, this report.

13. **Viva concepts:** Migration schema version control කරනවා; Seeder initial reference records insert කරනවා. One-to-one එකට unique FK; many-to-many එකට pivot table. Transaction එක related records සියල්ල succeed වීම හෝ rollback වීම enforce කරනවා. `attach` registration links insert කරනවා; `sync` edit කරන විට current category set replace කරනවා. Route model binding slug එක model එකකට map කරනවා. Authentication identity check කරනවා; authorization role/ownership check කරනවා. Backend validation browser bypass කළත් enforce වෙනවා. FK referential integrity enforce කරනවා; provider role/active category business rules application එක enforce කරනවා. MVC එකේ controller query/coordinating කරලා Blade display කරනවා. Eager loading N+1 queries වළක්වනවා.

Manual review: Provider register → select multiple categories → dashboard → edit. Customer login → Services → category → matching providers. Admin login → Manage service categories → add/edit/deactivate. Verify empty states, invalid category submission, inactive category behavior, keyboard/mobile forms and CSRF rejection. Admin provisioning is unchanged and requires an existing trusted Admin account.
