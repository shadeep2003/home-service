<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
class AccountProfile
{
    public static function rules(User $user, bool $required = true): array
    {
        return [
            'name' => [$required ? 'required' : 'sometimes', 'string', 'max:100'],
            'email' => [$required ? 'required' : 'sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ];
    }
    public static function save(User $user, array $data, ?\Closure $professional = null): void
    {
        $old = $user->profile_photo_path;
        $path = isset($data['photo']) ? $data['photo']->store('profile-photos', 'local') : null;
        if (isset($data['photo']) && !$path) { throw new \RuntimeException('Unable to save your photo.'); }
        try {
            DB::transaction(function () use ($user, $data, $path, $professional) {
                $user->fill(collect($data)->only(['name', 'email'])->all());
                if ($path) { $user->forceFill(['profile_photo_path' => $path]); }
                $user->save();
                if ($professional) { $professional(); }
            });
        } catch (\Throwable $error) {
            if ($path) { Storage::disk('local')->delete($path); }
            throw $error;
        }
        if ($path && $old) { Storage::disk('local')->delete($old); }
    }
}
