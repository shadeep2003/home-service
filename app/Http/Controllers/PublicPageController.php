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
        $providers = $category->providers()->where('role', 'provider')->whereNull('suspended_at')->whereHas('providerProfile')
            ->with('providerProfile')->withCount('reviews')->withAvg('reviews', 'rating')->orderBy('name')->orderBy('users.id')->paginate(12)->withQueryString();
        return view('public.category', compact('category', 'providers'));
    }
    public function about(): View { return view('public.about'); }
    public function contact(): View { return view('public.contact'); }
}
