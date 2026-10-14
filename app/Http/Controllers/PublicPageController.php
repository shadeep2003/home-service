<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicPageController
{
    public function home(): View { return view('welcome', ['categories' => \App\Models\ServiceCategory::active()->orderBy('id')->limit(6)->get()]); }
    public function services(): View { return view('public.services', ['categories' => \App\Models\ServiceCategory::active()->orderBy('name')->paginate(12)->withQueryString()]); }
    public function category(\App\Models\ServiceCategory $category): View
    {
        abort_unless($category->is_active, 404);
        $subcategories = $category->subcategories()->with('category')->where('is_active', true)->orderBy('name')->paginate(12)->withQueryString();
        return view('public.category', compact('category', 'subcategories'));
    }
    public function subcategory(\App\Models\ServiceCategory $category, \App\Models\ServiceSubcategory $subcategory, \Illuminate\Http\Request $request): View
    {
        abort_unless($category->is_active && $subcategory->is_active && $subcategory->service_category_id === $category->id, 404);
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $providers = $subcategory->providers()->where('role', 'provider')->whereNull('suspended_at')->whereHas('providerProfile')
            ->when($data['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', '%'.$search.'%')->orWhereHas('providerProfile', fn ($profile) => $profile->where('service_area', 'like', '%'.$search.'%'));
                });
            })
            ->with('providerProfile')->withCount('reviews')->withAvg('reviews', 'rating')->orderBy('name')->orderBy('users.id')->paginate(12)->withQueryString();
        return view('public.subcategory', compact('category', 'subcategory', 'providers'));
    }
    public function about(): View { return view('public.about'); }
    public function contact(): View { return view('public.contact'); }
}
