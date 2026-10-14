<?php
namespace Tests\Feature;
use App\Models\ServiceCategory;
use App\Models\ServiceSubcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class ServiceSubcategoriesTest extends TestCase
{
    use RefreshDatabase;
    private function category(string $name = 'Plumbing', bool $active = true): ServiceCategory {
        return ServiceCategory::create(['name' => $name, 'slug' => strtolower($name), 'is_active' => $active]);
    }
    private function provider(array $data = []): User {
        $provider = User::factory()->create($data + ['role' => 'provider']);
        $provider->providerProfile()->create(['phone' => '0771234567', 'service_area' => 'Colombo']);
        return $provider;
    }
    public function test_owner_creates_edits_and_removes_offering_without_losing_reviews_or_discoverability(): void {
        $category = $this->category(); $provider = $this->provider();
        $this->actingAs($provider)->post('/provider/subcategories', ['service_category_id' => $category->id, 'name' => ' Water pump repair ', 'owner_id' => 999, 'is_active' => false])->assertRedirect();
        $subcategory = ServiceSubcategory::firstOrFail();
        $this->assertSame($provider->id, $subcategory->owner_id);
        $this->assertTrue($subcategory->is_active);
        $this->assertSame('Water pump repair', $subcategory->name);
        $this->assertTrue($provider->serviceCategories()->whereKey($category->id)->exists());
        $this->assertTrue($provider->serviceSubcategories()->whereKey($subcategory->id)->exists());
        $this->get('/provider/dashboard')->assertSee('Manage subcategories');
        $this->get('/provider/subcategories')->assertSee('Water pump repair');
        $this->put('/provider/subcategories/'.$subcategory->id, ['service_category_id' => $category->id, 'name' => 'Pump servicing'])->assertRedirect();
        $review = new \App\Models\ProviderReview(['rating' => 5, 'comment' => 'Great']);
        $review->customer_id = User::factory()->create()->id;
        $provider->reviews()->save($review);
        $this->delete('/provider/subcategories/'.$subcategory->id)->assertRedirect();
        $this->assertDatabaseMissing('service_subcategories', ['id' => $subcategory->id]);
        $this->assertDatabaseCount('provider_reviews', 1);
        $general = ServiceSubcategory::firstOrFail();
        $this->assertSame('General Plumbing', $general->name);
        $this->get(route('services.subcategory', [$category, $general]))->assertSee($provider->name);
    }
    public function test_ownership_parent_and_duplicate_validation(): void {
        $category = $this->category(); $owner = $this->provider(); $other = $this->provider();
        $this->post('/provider/subcategories', [])->assertRedirect('/login');
        $this->get('/provider/subcategories')->assertRedirect('/login');
        $payload = ['service_category_id' => $category->id, 'name' => 'Pump repair'];
        $this->actingAs($owner)->post('/provider/subcategories', $payload)->assertRedirect();
        $subcategory = ServiceSubcategory::firstOrFail();
        $this->actingAs($other)->put('/provider/subcategories/'.$subcategory->id, $payload)->assertForbidden();
        $this->delete('/provider/subcategories/'.$subcategory->id)->assertForbidden();
        $this->post('/provider/subcategories', array_replace($payload, ['name' => ' PUMP REPAIR ']))->assertSessionHasErrors('name_key');
        $inactive = $this->category('Electrical', false);
        $this->post('/provider/subcategories', ['service_category_id' => $inactive->id, 'name' => 'House wiring'])->assertSessionHasErrors('service_category_id');
        $this->post('/provider/subcategories', ['service_category_id' => $category->id, 'name' => str_repeat('a', 101)])->assertSessionHasErrors('name');
        $this->post('/provider/subcategories', [])->assertSessionHasErrors(['name', 'service_category_id']);
        $this->post('/admin/categories', [])->assertForbidden();
        $this->actingAs(User::factory()->create())->post('/provider/subcategories', $payload)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->post('/provider/subcategories', $payload)->assertForbidden();
        $this->actingAs($owner)->put('/provider/subcategories/'.$subcategory->id, ['service_category_id' => $this->category('Gardening')->id, 'name' => 'Pump repair'])->assertStatus(422);
        $subcategory->providers()->attach($other);
        $this->delete('/provider/subcategories/'.$subcategory->id)->assertSessionHasErrors('subcategory');
        $this->assertDatabaseHas('provider_subcategories', ['provider_id' => $other->id, 'service_subcategory_id' => $subcategory->id]);
    }
    public function test_public_navigation_filter_search_and_pagination(): void {
        $category = $this->category(); $owner = $this->provider(['name' => 'Pump Specialist']);
        $this->actingAs($owner)->post('/provider/subcategories', ['service_category_id' => $category->id, 'name' => 'Pump repair'])->assertRedirect();
        $subcategory = ServiceSubcategory::firstOrFail();
        $url = route('services.subcategory', [$category, $subcategory]);
        \Illuminate\Support\Facades\Auth::logout();
        $this->get('/services/plumbing')->assertSee('Pump repair')->assertSee($url)->assertDontSee('Pump Specialist');
        $suspended = $this->provider(['name' => 'Suspended Expert', 'suspended_at' => now()]);
        $missing = User::factory()->create(['role' => 'provider', 'name' => 'Missing Profile']);
        $customer = User::factory()->create(['name' => 'Customer Only']);
        $subcategory->providers()->attach([$suspended->id, $missing->id, $customer->id]);
        $unrelated = $this->provider(['name' => 'Other Specialist']);
        \App\Services\ProviderServices::sync($unrelated, [$category->id]);
        $this->get($url)->assertSee('Pump Specialist')->assertSee(route('providers.reviews', $owner))->assertDontSee('Suspended Expert')->assertDontSee('Missing Profile')->assertDontSee('Customer Only')->assertDontSee('Other Specialist');
        for ($i = 0; $i < 13; $i++) { $subcategory->providers()->attach($this->provider(['name' => 'Search Expert '.$i])); }
        $this->get($url.'?search=Search')->assertSee('Search Expert')->assertDontSee('Pump Specialist')->assertSee('search=Search', false)->assertSee('page=2', false);
        $this->get($url.'?search=absent')->assertSee('No matching providers');
        $this->get(route('services.subcategory', [$this->category('Cleaning'), $subcategory]))->assertNotFound();
        $subcategory->update(['is_active' => false]);
        $this->get($url)->assertNotFound(); $this->get('/services/plumbing')->assertDontSee('Pump repair');
        $subcategory->update(['is_active' => true]); $category->update(['is_active' => false]);
        $this->get($url)->assertNotFound(); $this->get('/services/plumbing')->assertNotFound();
    }
    public function test_backfill_is_repeatable_preserves_accounts_and_reuses_matching_offerings(): void {
        $category = $this->category(); $inactive = $this->category('Electrical', false);
        $first = $this->provider(); $second = $this->provider(['email_verified_at' => null, 'suspended_at' => now(), 'profile_photo_path' => 'existing-photo.png']);
        $first->serviceCategories()->attach([$category->id, $inactive->id]); $second->serviceCategories()->attach($category);
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        $profiles = DB::table('provider_profiles')->get()->toJson();
        $migration = require database_path('migrations/2026_10_11_000002_backfill_service_subcategories.php');
        $migration->up(); $migration->up();
        $this->assertDatabaseCount('service_subcategories', 2); $this->assertDatabaseCount('provider_subcategories', 3);
        $this->assertDatabaseCount('provider_services', 3);
        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
        $this->assertSame($profiles, DB::table('provider_profiles')->get()->toJson());
        $general = ServiceSubcategory::where('service_category_id', $category->id)->firstOrFail();
        $this->get('/services/plumbing')->assertSee('General Plumbing');
        $this->get(route('services.subcategory', [$category, $general]))->assertSee($first->name)->assertDontSee($second->name);
        $third = $this->provider();
        $this->actingAs($third)->post('/provider/subcategories', ['service_category_id' => $category->id, 'name' => 'Custom repair'])->assertRedirect();
        $migration->up();
        $this->assertSame(1, $third->serviceSubcategories()->count());
        $migration->down(); $this->assertDatabaseCount('provider_subcategories', 4);
        $schema = require database_path('migrations/2026_10_11_000001_create_service_subcategories.php');
        $schema->down();
        $this->assertDatabaseCount('provider_services', 4);
        $schema->up(); $migration->up();
        $this->assertDatabaseCount('provider_subcategories', 4);
        $this->assertSame($before, DB::table('users')->whereIn('id', [$first->id, $second->id])->orderBy('id')->get()->toJson());
    }
}
