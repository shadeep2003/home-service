<?php
namespace Database\Seeders;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class ServiceCatalogueAssetsSeeder extends Seeder
{
    public function run(): void
    {
        $manifest = json_decode(file_get_contents(database_path('seeders/fixtures/service-catalogue.json')), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($manifest) {
            foreach ($manifest['assets'] as $asset) {
                if (!Storage::disk('service_images')->exists($asset['image'])) {
                    throw new \RuntimeException('Missing catalogue image: '.$asset['image']);
                }
                $category = ServiceCategory::where('slug', $asset['category_slug'])->first();
                if (!$category) { continue; }
                $record = isset($asset['subcategory_key'])
                    ? $category->subcategories()->where('name_key', $asset['subcategory_key'])->first()
                    : $category;
                if (!$record) { continue; }
                $data = [];
                if (!$record->image) { $data += ['image' => $asset['image'], 'image_alt' => $asset['alt']]; }
                if (blank($record->description)) { $data['description'] = $asset['description']; }
                if ($data) { $record->forceFill($data)->save(); }
            }
            // Include any newer records without hardcoding category names.
            foreach (ServiceCategory::with('subcategories')->get() as $category) {
                if (!$category->image) {
                    $category->forceFill(['image' => $manifest['default_asset']['image'], 'image_alt' => $manifest['default_asset']['alt']])->save();
                }
                if (blank($category->description)) {
                    $category->description = 'Explore local '.mb_strtolower($category->name).' services for a well-cared-for home.';
                    $category->save();
                }
                foreach ($category->subcategories as $subcategory) {
                    $data = [];
                    if (!$subcategory->image && $category->image) {
                        $data += ['image' => $category->image, 'image_alt' => $category->image_alt];
                    }
                    if (blank($subcategory->description)) {
                        $data['description'] = 'Find local professionals offering '.mb_strtolower($subcategory->name).' for your home. Contact a provider to discuss your needs.';
                    }
                    if ($data) { $subcategory->forceFill($data)->save(); }
                }
            }
        });
    }
}
