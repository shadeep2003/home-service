<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ProfilePhotoController
{
    public function update(Request $request)
    {
        $request->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096']]);
        $user = $request->user();
        $oldPath = $user->profile_photo_path;
        $path = $request->file('photo')->store('profile-photos', 'local');
        abort_unless($path, 500, 'Unable to save your photo.');
        try {
            $user->forceFill(['profile_photo_path' => $path])->save();
        } catch (\Throwable $error) {
            Storage::disk('local')->delete($path);
            throw $error;
        }
        if ($oldPath) { Storage::disk('local')->delete($oldPath); }
        return back()->with('photo_status', 'Profile photo updated.');
    }
    public function show(User $user)
    {
        if (!$user->profile_photo_path || !Storage::disk('local')->exists($user->profile_photo_path)) {
            return response()->file(public_path('images/default-avatar.svg'));
        }
        return response()->file(Storage::disk('local')->path($user->profile_photo_path), ['X-Content-Type-Options' => 'nosniff']);
    }
}
