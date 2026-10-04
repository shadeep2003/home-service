<?php
namespace App\Http\Controllers;
use App\Http\Requests\ProviderProfileRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ProviderProfileController
{
    public function dashboard(Request $request)
    {
        return view('dashboard.provider', ['provider' => $request->user()->load('providerProfile', 'serviceCategories')]);
    }
    public function edit(Request $request)
    {
        return view('provider.edit', ['provider' => $request->user()->load('providerProfile', 'serviceCategories'), 'categories' => ServiceCategory::active()->orderBy('name')->get()]);
    }
    public function update(ProviderProfileRequest $request)
    {
        $data = $request->validated();
        \App\Services\AccountProfile::save($request->user(), $data, function () use ($request, $data) {
            $request->user()->providerProfile()->updateOrCreate([], collect($data)->except(['category_ids', 'name', 'email', 'photo'])->all());
            $request->user()->serviceCategories()->sync($data['category_ids']);
        });
        return redirect()->route('provider.dashboard')->with('status', 'Profile updated.');
    }
}
