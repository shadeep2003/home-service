<?php
namespace App\Http\Controllers;
use App\Http\Requests\ServiceSubcategoryRequest;
use App\Models\ServiceCategory;
use App\Models\ServiceSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class ServiceSubcategoryController
{
    public function index(Request $request) {
        return view('provider.subcategories', ['subcategories' => $request->user()->ownedSubcategories()->with('category')->orderBy('name')->get(), 'categories' => ServiceCategory::active()->orderBy('name')->get()]);
    }
    public function store(ServiceSubcategoryRequest $request) { return $this->save($request, new ServiceSubcategory); }
    public function update(ServiceSubcategoryRequest $request, ServiceSubcategory $subcategory) {
        // Moving an offering could hide it from its existing directory and associations.
        abort_unless($subcategory->service_category_id === (int) $request->validated('service_category_id'), 422);
        return $this->save($request, $subcategory);
    }
    private function save(ServiceSubcategoryRequest $request, ServiceSubcategory $subcategory) {
        DB::transaction(function () use ($request, $subcategory) {
            $category = ServiceCategory::whereKey($request->validated('service_category_id'))->lockForUpdate()->firstOrFail();
            abort_unless($category->is_active, 422);
            if ($category->subcategories()->where('name_key', $request->validated('name_key'))->when($subcategory->exists, fn ($query) => $query->where('id', '!=', $subcategory->id))->exists()) {
                throw ValidationException::withMessages(['name' => 'This service name already exists in this category.']);
            }
            $subcategory->fill($request->safe()->only(['name', 'description']));
            $subcategory->name_key = $request->validated('name_key');
            $subcategory->service_category_id = $category->id;
            $subcategory->owner_id = $request->user()->id;
            $subcategory->save();
            $subcategory->providers()->syncWithoutDetaching([$request->user()->id]);
            $request->user()->serviceCategories()->syncWithoutDetaching([$category->id]);
        });
        return redirect()->route('provider.subcategories.index')->with('status', 'Subcategory saved.');
    }
    public function destroy(Request $request, ServiceSubcategory $subcategory) {
        abort_unless(!$request->user()->suspended_at && $subcategory->owner_id === $request->user()->id, 403);
        DB::transaction(function () use ($request, $subcategory) {
            $subcategory = ServiceSubcategory::whereKey($subcategory->id)->lockForUpdate()->firstOrFail();
            if ($subcategory->providers()->where('users.id', '!=', $request->user()->id)->exists()) {
                throw ValidationException::withMessages(['subcategory' => 'Other providers offer this subcategory; it cannot be removed.']);
            }
            // Reviews belong to the provider, never to the offering. Keep the provider discoverable.
            $subcategory->providers()->detach(); $subcategory->delete();
            \App\Services\ProviderServices::sync($request->user(), $request->user()->serviceCategories()->pluck('service_categories.id')->all());
        });
        return redirect()->route('provider.subcategories.index')->with('status', 'Subcategory removed.');
    }
}
