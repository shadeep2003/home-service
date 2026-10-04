<?php
namespace Tests\Feature;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ServiceProviderFoundationTest extends TestCase
{
    use RefreshDatabase;
    private function category(string $slug = 'electrical', bool $active = true): ServiceCategory
    {
        return ServiceCategory::create(['name' => ucfirst($slug), 'slug' => $slug, 'is_active' => $active]);
    }
    private function registration(array $overrides = []): array
    {
        return array_replace(['name' => 'Real Provider', 'email' => 'provider@example.test', 'role' => 'provider', 'password' => 'Password123', 'password_confirmation' => 'Password123', 'phone' => '0771234567', 'service_area' => 'Colombo', 'biography' => 'Home electrical repairs', 'experience_years' => 3], $overrides);
    }
    public function test_provider_requires_profile_and_categories(): void
    {
        $data = $this->registration(); unset($data['phone'], $data['service_area']);
        $this->post('/register', $data)->assertSessionHasErrors(['phone', 'service_area', 'category_ids']);
        $this->assertDatabaseCount('users', 0); $this->assertDatabaseCount('provider_profiles', 0);
    }
    public function test_invalid_inactive_and_duplicate_category_ids_are_rejected(): void
    {
        $inactive = $this->category('inactive', false); $active = $this->category();
        foreach ([[9999], [$inactive->id], [$active->id, $active->id]] as $ids) {
            $this->post('/register', $this->registration(['category_ids' => $ids]))->assertSessionHasErrors('category_ids.0');
        }
        $this->assertDatabaseCount('users', 0); $this->assertDatabaseCount('provider_services', 0);
    }
    public function test_provider_registers_multiple_categories_and_sees_dashboard(): void
    {
        $first = $this->category(); $second = $this->category('plumbing');
        $this->post('/register', $this->registration(['category_ids' => [$first->id, $second->id]]))->assertRedirect('/dashboard');
        $user = User::firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(2, $user->serviceCategories()->count());
        $this->assertDatabaseHas('provider_profiles', ['user_id' => $user->id, 'service_area' => 'Colombo']);
        $this->get('/provider/dashboard')->assertOk()->assertSee('Electrical')->assertSee('Plumbing')->assertSee('Colombo');
    }
    public function test_registration_rolls_back_when_profile_creation_fails(): void
    {
        $category = $this->category();
        \App\Models\ProviderProfile::creating(function () { throw new \RuntimeException('Profile persistence failed'); });
        $this->withoutExceptionHandling();
        try {
            $this->post('/register', $this->registration(['category_ids' => [$category->id]]));
            $this->fail('Expected profile persistence failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Profile persistence failed', $exception->getMessage());
        } finally {
            \App\Models\ProviderProfile::flushEventListeners();
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('provider_profiles', 0);
        $this->assertDatabaseCount('provider_services', 0);
        $this->assertGuest();
    }
    public function test_customer_payload_cannot_create_provider_records(): void
    {
        $this->post('/register', $this->registration(['role' => 'customer', 'category_ids' => [9999]]))->assertRedirect('/dashboard');
        $this->assertDatabaseCount('users', 1); $this->assertDatabaseCount('provider_profiles', 0); $this->assertDatabaseCount('provider_services', 0);
    }
    public function test_database_categories_are_visible_and_inactive_categories_are_hidden(): void
    {
        $active = $this->category('roof-care'); $this->category('hidden-service', false);
        $this->get('/services')->assertOk()->assertSee('Roof-care')->assertSee('/services/roof-care')->assertDontSee('Hidden-service');
        $this->get('/register?role=provider')->assertOk()->assertSee('Roof-care')->assertDontSee('Hidden-service');
        $this->get('/services/hidden-service')->assertNotFound(); $this->get('/services/missing')->assertNotFound();
    }
    public function test_category_lists_only_matching_real_providers_for_customer(): void
    {
        $electrical = $this->category(); $plumbing = $this->category('plumbing');
        foreach ([[$electrical, 'Electrical Expert'], [$plumbing, 'Plumbing Expert']] as [$category, $name]) {
            $provider = User::factory()->create(['name' => $name, 'role' => 'provider']);
            $provider->providerProfile()->create(['phone' => '0771234567', 'service_area' => 'Kandy']);
            $provider->serviceCategories()->attach($category);
        }
        $this->actingAs(User::factory()->create())->get('/services/electrical')->assertOk()->assertSee('Electrical Expert')->assertSee('Kandy')->assertDontSee('Plumbing Expert');
    }
    public function test_directory_shows_provider_work_state_and_contact_links(): void
    {
        $category = $this->category();
        $provider = User::factory()->create(['name' => 'Local Expert', 'role' => 'provider']);
        $provider->serviceCategories()->attach($category);
        $profile = $provider->providerProfile()->create([
            'phone' => '077 123 4567', 'service_area' => 'Kandy',
            'is_available' => true, 'is_working' => true,
        ]);
        $this->get('/services/electrical')->assertOk()
            ->assertSee('Currently working')->assertSee('tel:+94771234567', false)
            ->assertSee('https://wa.me/94771234567?text=', false)
            ->assertSee('Status set by provider.')->assertDontSee('Available for enquiries');
        $profile->update(['is_working' => false, 'is_available' => false]);
        $this->get('/services/electrical')->assertSee('Currently unavailable');
        $profile->update(['is_available' => true]);
        $this->get('/services/electrical')->assertSee('Available for enquiries');
    }

    public function test_provider_can_update_working_state(): void
    {
        $category = $this->category();
        $provider = User::factory()->create(['role' => 'provider']);
        $this->actingAs($provider)->put('/provider/profile', [
            'phone' => '0771234567', 'service_area' => 'Galle',
            'category_ids' => [$category->id], 'is_available' => '1', 'is_working' => '1',
        ])->assertRedirect('/provider/dashboard');
        $this->assertDatabaseHas('provider_profiles', ['user_id' => $provider->id, 'is_working' => true]);
        $this->get('/provider/dashboard')->assertSee('Currently working on a job');
    }

    public function test_contact_numbers_support_local_and_international_formats(): void
    {
        foreach (['0771234567' => '94771234567', '+94 77 123 4567' => '94771234567',
            '0094771234567' => '94771234567', '+44 7700 900123' => '447700900123',
            '1234567' => null] as $phone => $expected) {
            $profile = new \App\Models\ProviderProfile(['phone' => (string) $phone]);
            $this->assertSame($expected, $profile->contactNumber());
        }
    }

    public function test_empty_states_render(): void
    {
        $this->get('/services')->assertOk()->assertSee('No active service categories');
        $this->category(); $this->get('/services/electrical')->assertOk()->assertSee('No providers yet');
    }
    public function test_provider_updates_own_profile_and_replaces_categories(): void
    {
        $first = $this->category(); $second = $this->category('plumbing');
        $provider = User::factory()->create(['role' => 'provider']);
        $provider->serviceCategories()->attach($first);
        $this->actingAs($provider)->get('/provider/profile/edit')->assertOk();
        $this->put('/provider/profile', ['phone' => '0771234567', 'service_area' => 'Galle', 'category_ids' => [$second->id], 'is_available' => '0'])->assertRedirect('/provider/dashboard');
        $this->assertDatabaseHas('provider_profiles', ['user_id' => $provider->id, 'service_area' => 'Galle', 'is_available' => false]);
        $this->assertSame([$second->id], $provider->serviceCategories()->pluck('service_categories.id')->all());
        $this->put('/provider/profile', ['phone' => '0771234567', 'service_area' => 'Galle', 'category_ids' => [9999], 'is_available' => '1'])->assertSessionHasErrors('category_ids.0');
        $this->assertDatabaseHas('provider_profiles', ['user_id' => $provider->id, 'is_available' => false]);
    }
    public function test_customer_cannot_edit_provider_profile_or_manage_categories(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/provider/profile/edit')->assertForbidden(); $this->put('/provider/profile', [])->assertForbidden();
        $this->get('/admin/categories')->assertForbidden(); $this->post('/admin/categories', [])->assertForbidden();
    }
    public function test_admin_creates_edits_and_deactivates_category(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/categories/create')->assertOk();
        $this->post('/admin/categories', ['name' => 'Roof Care', 'slug' => 'roof-care', 'icon' => 'check', 'is_active' => '1'])->assertRedirect('/admin/categories');
        $category = ServiceCategory::firstOrFail();
        $this->get('/admin/categories')->assertOk()->assertSee('Roof Care');
        $this->get('/admin/categories/'.$category->id.'/edit')->assertOk();
        $this->put('/admin/categories/'.$category->id, ['name' => 'Roof Care', 'slug' => 'roof-care', 'icon' => 'check', 'is_active' => '0'])->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('service_categories', ['id' => $category->id, 'is_active' => false]);
        $this->post('/admin/categories', ['name' => 'Duplicate', 'slug' => 'roof-care', 'is_active' => '1'])->assertSessionHasErrors('slug');
    }
    public function test_seeding_is_repeatable_and_preserves_admin_edits(): void
    {
        $this->seed(ServiceCategorySeeder::class);
        ServiceCategory::where('slug', 'electrical')->update(['description' => 'Edited by admin', 'is_active' => false]);
        $this->seed(ServiceCategorySeeder::class);
        $this->assertDatabaseCount('service_categories', 6);
        $this->assertDatabaseHas('service_categories', ['slug' => 'electrical', 'description' => 'Edited by admin', 'is_active' => false]);
    }
}
