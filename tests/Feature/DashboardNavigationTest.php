<?php
namespace Tests\Feature;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;
    private function navigation($response): \DOMXPath
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors(); libxml_use_internal_errors($previous);
        return new \DOMXPath($document);
    }
    private function activeLinks($response): array
    {
        $links = $this->navigation($response)->query('//aside[@id="dashboard-sidebar"]//nav//a[@aria-current="page"]');
        return array_map(fn ($link) => $link->getAttribute('aria-label'), iterator_to_array($links));
    }
    public function test_each_role_gets_only_implemented_navigation_and_no_duplicate_public_navbar(): void
    {
        $expected = [
            'customer' => ['Dashboard', 'Browse services', 'My profile'],
            'provider' => ['Provider dashboard', 'My services', 'Browse marketplace', 'Edit provider profile'],
            'admin' => ['Admin dashboard', 'User management', 'Service providers', 'Service categories', 'My profile'],
        ];
        foreach ($expected as $role => $labels) {
            $user = User::factory()->create(['role' => $role, 'name' => 'Taylor Home']);
            $response = $this->actingAs($user)->get(route($role.'.dashboard'))->assertOk();
            $response->assertSee('css/dashboard.css', false)->assertSee('js/dashboard-navigation.js', false)
                ->assertSee('data-dashboard-sidebar', false)->assertSee('aria-controls="dashboard-sidebar"', false)
                ->assertDontSee('aria-label="Main navigation"', false)->assertDontSee('class="site-header"', false)
                ->assertDontSee('My Bookings')->assertDontSee('Booking History')->assertDontSee('Active Jobs');
            $dom = $this->navigation($response);
            $links = $dom->query('//aside[@id="dashboard-sidebar"]//nav//a');
            $this->assertSame($labels, array_map(fn ($link) => $link->getAttribute('aria-label'), iterator_to_array($links)));
            $this->assertSame([$labels[0]], $this->activeLinks($response));
            foreach ($links as $link) {
                $this->assertNotEmpty($link->getAttribute('title'));
                // Every destination is an existing GET page the role can reach.
                $this->get($link->getAttribute('href'))->assertOk();
            }
            $sidebar = $dom->query('//aside[@id="dashboard-sidebar"]')->item(0)->textContent;
            $this->assertStringContainsString('Taylor Home', $sidebar);
            $this->assertStringNotContainsString($user->email, $sidebar);
            $this->assertSame(1, $dom->query('//aside//form[@method="POST"]')->length);
            $this->assertSame(1, $dom->query('//aside//form/input[@name="_token"]')->length);
            auth()->logout();
        }
    }
    public function test_nested_pages_and_existing_admin_filters_select_one_navigation_item(): void
    {
        $category = ServiceCategory::create(['name' => 'Plumbing', 'slug' => 'plumbing']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        foreach (['/admin/categories', '/admin/categories/create', '/admin/categories/'.$category->id.'/edit'] as $url) {
            $this->assertSame(['Service categories'], $this->activeLinks($this->get($url)->assertOk()));
        }
        $this->assertSame(['Service providers'], $this->activeLinks($this->get('/admin/dashboard?role=provider')->assertOk()));
        $this->assertSame(['User management'], $this->activeLinks($this->get('/admin/dashboard?role=customer')->assertOk()));
        $this->assertSame(['User management'], $this->activeLinks($this->get('/admin/dashboard?search=Taylor')->assertOk()));
        $this->assertSame(['My profile'], $this->activeLinks($this->get('/profile/edit')->assertOk()));
        $this->get('/admin/dashboard')->assertSee('id="accounts"', false)->assertSee('data-dashboard-section="accounts"', false);
        $provider = User::factory()->create(['role' => 'provider']);
        $this->actingAs($provider);
        $this->assertSame(['My services'], $this->activeLinks($this->get('/provider/subcategories')->assertOk()));
        $this->assertSame(['Edit provider profile'], $this->activeLinks($this->get('/provider/profile/edit')->assertOk()));
    }
    public function test_user_card_uses_initials_or_the_existing_profile_photo_endpoint(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['name' => 'Alexandra Wijesinghe', 'role' => 'customer']);
        $response = $this->actingAs($user)->get('/customer/dashboard')->assertOk();
        $dom = $this->navigation($response);
        $initials = $dom->query('//aside//span[contains(@class,"dashboard-user-card__initials")]');
        $this->assertSame('AW', trim($initials->item(0)->textContent));
        $user->forceFill(['profile_photo_path' => 'profile-photos/test-avatar.png'])->save();
        Storage::disk('local')->put($user->profile_photo_path, file_get_contents(database_path('seeders/fixtures/avatar-0.png')));
        $response = $this->get('/customer/dashboard')->assertOk();
        $dom = $this->navigation($response);
        $image = $dom->query('//aside//img[contains(@class,"dashboard-user-card__avatar")]')->item(0);
        $this->assertSame($user->profilePhotoUrl(), $image->getAttribute('src'));
        $this->get($image->getAttribute('src'))->assertOk()->assertHeader('content-type', 'image/png');
    }
    public function test_sidebar_adds_no_database_queries_and_does_not_print_private_account_fields(): void
    {
        $user = User::factory()->create(['role' => 'provider']);
        DB::enableQueryLog(); DB::flushQueryLog();
        $html = Blade::render('<x-dashboard.sidebar :user="$user" />', ['user' => $user]);
        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertStringNotContainsString($user->email, $html);
        $this->assertStringNotContainsString($user->password, $html);
    }
    public function test_public_pages_keep_the_public_navbar_even_for_authenticated_users(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'provider']));
        foreach (['/', '/services', '/about', '/contact'] as $url) {
            $this->get($url)->assertOk()->assertSee('aria-label="Main navigation"', false)
                ->assertDontSee('data-dashboard-sidebar', false)->assertDontSee('js/dashboard-navigation.js', false);
        }
        auth()->logout();
        foreach (['/login', '/register'] as $url) {
            $this->get($url)->assertOk()->assertSee('aria-label="Main navigation"', false)->assertDontSee('data-dashboard-sidebar', false);
        }
    }
    public function test_sidebar_logout_and_existing_authorization_still_work(): void
    {
        $this->get('/customer/dashboard')->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['role' => 'customer']));
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/categories')->assertForbidden();
        $this->get('/provider/subcategories')->assertForbidden();
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest(); $this->get('/customer/dashboard')->assertRedirect('/login');
    }
}
