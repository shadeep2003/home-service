<?php
namespace App\Http\Controllers;
use App\Enums\Role;
use App\Services\AccountProfile;
use Illuminate\Http\Request;
class AccountProfileController
{
    public function edit(Request $request)
    {
        if ($request->user()->role === Role::Provider) { return redirect()->route('provider.profile.edit'); }
        return view('profile.edit', ['user' => $request->user()]);
    }
    public function update(Request $request)
    {
        if ($request->user()->role === Role::Provider) { return redirect()->route('provider.profile.edit'); }
        $data = $request->validate(AccountProfile::rules($request->user()));
        AccountProfile::save($request->user(), $data);
        return redirect()->route('profile.edit')->with('status', 'Your profile has been updated.');
    }
}
