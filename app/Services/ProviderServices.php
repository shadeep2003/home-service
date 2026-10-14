<?php
namespace App\Services;
use App\Models\ServiceCategory;
use App\Models\ServiceSubcategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class ProviderServices
{
    public static function sync(User $provider, array $ids): void
    {
        DB::transaction(function () use ($provider, $ids) {
            $categories = ServiceCategory::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
            // Profile edits retain custom offerings, including those with inactive parents.
            $customParents = $provider->ownedSubcategories()->pluck('service_category_id')->all();
            $provider->serviceCategories()->sync(array_unique(array_merge($ids, $customParents)));
            foreach ($categories as $category) {
                if ($provider->serviceSubcategories()->where('service_category_id', $category->id)->exists()) { continue; }
                $name = mb_substr('General '.$category->name, 0, 100);
                $subcategory = $category->subcategories()->where('name_key', mb_strtolower($name))->first();
                if (!$subcategory) {
                    $subcategory = new ServiceSubcategory(['name' => $name]);
                    $subcategory->name_key = mb_strtolower($name);
                    $category->subcategories()->save($subcategory);
                }
                $provider->serviceSubcategories()->syncWithoutDetaching([$subcategory->id]);
            }
            $remove = $provider->serviceSubcategories()->whereNull('owner_id')->whereNotIn('service_category_id', $ids)->pluck('service_subcategories.id');
            $provider->serviceSubcategories()->detach($remove);
        });
    }
}
