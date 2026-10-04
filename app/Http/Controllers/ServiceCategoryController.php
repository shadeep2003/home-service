<?php
namespace App\Http\Controllers;
use App\Models\ServiceCategory;
use App\Http\Requests\ServiceCategoryRequest;
class ServiceCategoryController
{
    public function index() { return view('admin.categories.index', ['categories' => ServiceCategory::orderBy('name')->paginate(20)]); }
    public function create() { return view('admin.categories.form', ['category' => new ServiceCategory(['is_active' => true])]); }
    public function store(ServiceCategoryRequest $request)
    {
        ServiceCategory::create($request->validated());
        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }
    public function edit(ServiceCategory $category) { return view('admin.categories.form', compact('category')); }
    public function update(ServiceCategoryRequest $request, ServiceCategory $category)
    {
        $category->update($request->validated());
        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }
}
