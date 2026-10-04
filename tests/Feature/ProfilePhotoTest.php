<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;
    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('avatar.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1cAAAAASUVORK5CYII='));
    }
    public function test_users_can_upload_replace_and_serve_their_own_photo(): void
    {
        Storage::fake('local');
        foreach (['customer', 'provider', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->post(route('profile.photo.update'), ['photo' => $this->photo(), 'user_id' => 999])->assertRedirect();
            $old = $user->fresh()->profile_photo_path;
            Storage::disk('local')->assertExists($old);
            $this->get(route('profile.photo.show', $user))->assertOk()->assertHeader('content-type', 'image/png');
            $this->post(route('profile.photo.update'), ['photo' => $this->photo()])->assertRedirect();
            Storage::disk('local')->assertMissing($old);
            Storage::disk('local')->assertExists($user->fresh()->profile_photo_path);
        }
    }
    public function test_missing_photo_uses_default_and_invalid_uploads_are_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $this->assertStringContainsString('default-avatar.svg', $user->profilePhotoUrl());
        $this->get(route('profile.photo.show', $user))->assertOk()->assertHeader('content-type', 'image/svg+xml');
        $this->post(route('profile.photo.update'), ['photo' => $this->photo()])->assertRedirect(route('login'));
        $this->actingAs($user)->post(route('profile.photo.update'), ['photo' => UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')])->assertSessionHasErrors('photo');
        $this->post(route('profile.photo.update'), ['photo' => $this->photo()->size(2049)])->assertSessionHasErrors('photo');
        $this->assertNull($user->fresh()->profile_photo_path);
    }
}
