<?php
namespace Tests\Feature;
use App\Models\ServiceCategory;
use App\Models\ServiceSubcategory;
use Database\Seeders\ServiceCatalogueAssetsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class ServiceCataloguePresentationTest extends TestCase
{
    use RefreshDatabase;
    public function test_asset_installation_is_repeatable_and_preserves_existing_content_and_relationships(): void
    {
        $category = ServiceCategory::create(['name' => 'Admin-renamed electrical', 'slug' => 'electrical', 'description' => 'Admin description', 'is_active' => false]);
        $subcategory = new ServiceSubcategory(['name' => 'General Electrical', 'description' => 'Provider description']);
        $subcategory->name_key = 'general electrical';
        $category->subcategories()->save($subcategory);
        $provider = \App\Models\User::factory()->create(['role' => 'provider']);
        $subcategory->providers()->attach($provider);
        $accounts = DB::table('users')->get()->toJson();
        $links = DB::table('provider_subcategories')->get()->toJson();
        $this->seed(ServiceCatalogueAssetsSeeder::class);
        $category->refresh(); $subcategory->refresh();
        $this->assertSame('category-electrical.webp', $category->image);
        $this->assertSame('subcategory-general-electrical.webp', $subcategory->image);
        $this->assertSame('Admin description', $category->description);
        $this->assertSame('Provider description', $subcategory->description);
        $this->assertFalse($category->is_active);
        $this->assertSame('Admin-renamed electrical', $category->name);
        $category->forceFill(['image' => 'category-plumbing.webp', 'image_alt' => 'Custom administrator photo'])->save();
        $before = DB::table('service_categories')->get()->toJson();
        $this->seed(ServiceCatalogueAssetsSeeder::class);
        $this->assertSame($before, DB::table('service_categories')->get()->toJson());
        $this->assertSame($accounts, DB::table('users')->get()->toJson());
        $this->assertSame($links, DB::table('provider_subcategories')->get()->toJson());
    }
    public function test_missing_descriptions_and_images_are_filled_without_creating_catalogue_records(): void
    {
        $category = ServiceCategory::create(['name' => 'Plumbing', 'slug' => 'plumbing', 'is_active' => true]);
        $subcategory = new ServiceSubcategory(['name' => 'General Plumbing']);
        $subcategory->name_key = 'general plumbing'; $category->subcategories()->save($subcategory);
        $this->seed(ServiceCatalogueAssetsSeeder::class);
        $this->assertDatabaseCount('service_categories', 1); $this->assertDatabaseCount('service_subcategories', 1);
        $this->assertNotEmpty($category->fresh()->description);
        $this->assertNotEmpty($subcategory->fresh()->description);
        $this->assertNotEmpty($subcategory->fresh()->image_alt);
        $category->refresh(); $subcategory->refresh();
        $this->get('/services')->assertOk()->assertSee($category->imageUrl(), false)->assertSee('loading="lazy"', false)->assertSee('srcset=', false)->assertSee('Explore services');
        $this->get('/services/plumbing')->assertOk()->assertSee($subcategory->imageUrl(), false)->assertSee($subcategory->image_alt)->assertSee(route('services.subcategory', [$category, $subcategory]), false)->assertSee('View providers');
        $this->get(route('services.subcategory', [$category, $subcategory]))->assertOk()->assertSee('No providers yet')->assertSee('Explore other services');
    }
    public function test_new_and_missing_assets_use_real_local_images_with_correct_alt_text(): void
    {
        $category = ServiceCategory::create(['name' => 'Roof Care', 'slug' => 'roof-care']);
        $subcategory = new ServiceSubcategory(['name' => 'Roof maintenance']);
        $subcategory->name_key = 'roof maintenance'; $category->subcategories()->save($subcategory);
        $this->assertSame(asset('images/services/home-services.webp'), $category->imageUrl());
        $this->assertTrue(Storage::disk('service_images')->exists('home-services.webp'));
        $category->forceFill(['image' => 'category-plumbing.webp', 'image_alt' => 'A professional at work'])->save();
        $subcategory->forceFill(['image' => 'missing-photo.webp', 'image_alt' => 'Missing photo'])->save();
        $this->assertSame($category->imageUrl(), $subcategory->imageUrl());
        $this->assertSame('A professional at work', $subcategory->imageAlt());
        $this->assertStringContainsString('category-plumbing-480.webp', $subcategory->imageSrcset());
        $this->get('/services/roof-care')->assertOk()->assertDontSee('missing-photo.webp')->assertSee('Roof maintenance');
        $this->seed(ServiceCatalogueAssetsSeeder::class);
        $this->assertNotEmpty($category->fresh()->description);
        $this->assertNotEmpty($subcategory->fresh()->description);
    }
    public function test_catalogue_assets_are_local_webp_with_consistent_dimensions(): void
    {
        $manifest = json_decode(file_get_contents(database_path('seeders/fixtures/service-catalogue.json')), true);
        foreach (array_merge($manifest['assets'], [$manifest['default_asset']]) as $asset) {
            foreach ([$asset['image'] => [960, 540], str_replace('.webp', '-480.webp', $asset['image']) => [480, 270]] as $file => $dimensions) {
                $path = public_path('images/services/'.$file);
                $this->assertFileExists($path);
                $size = getimagesize($path);
                $this->assertSame($dimensions, [$size[0], $size[1]]);
                $this->assertSame('image/webp', $size['mime']);
                $this->assertLessThan(180000, filesize($path));
            }
            $this->assertNotEmpty($asset['alt']);
        }
    }
    public function test_image_schema_rollback_keeps_catalogue_and_provider_records(): void
    {
        $category = ServiceCategory::create(['name' => 'Cleaning', 'slug' => 'cleaning']);
        $subcategory = new ServiceSubcategory(['name' => 'General Cleaning']);
        $subcategory->name_key = 'general cleaning'; $category->subcategories()->save($subcategory);
        $provider = \App\Models\User::factory()->create(['role' => 'provider']);
        $subcategory->providers()->attach($provider);
        $migration = require database_path('migrations/2026_10_11_000003_add_service_catalogue_images.php');
        $migration->down();
        $this->assertDatabaseHas('service_categories', ['id' => $category->id, 'name' => 'Cleaning']);
        $this->assertDatabaseHas('provider_subcategories', ['provider_id' => $provider->id, 'service_subcategory_id' => $subcategory->id]);
        $migration->up();
        $this->assertDatabaseHas('service_subcategories', ['id' => $subcategory->id, 'image' => null]);
    }
}
