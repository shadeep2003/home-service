<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AccountProfileTest extends TestCase
{
    use RefreshDatabase;
    public function test_customer_can_edit_own_details_without_uploading_a_photo(): void
    {
        $user = User::factory()->create(); $other = User::factory()->create();
        $this->actingAs($user)->get('/profile/edit')->assertOk()->assertSee('Profile picture (optional)');
        $this->put('/profile', ['name' => 'New Name', 'email' => 'new@example.com', 'role' => 'admin', 'user_id' => $other->id])->assertRedirect('/profile/edit');
        $this->assertSame('New Name', $user->fresh()->name);
        $this->assertSame('customer', $user->fresh()->role->value);
        $this->assertNotSame('New Name', $other->fresh()->name);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }
    public function test_provider_saves_personal_and_professional_fields_together(): void
    {
        $user = User::factory()->create(['role' => 'provider']);
        $category = ServiceCategory::create(['name' => 'Electrical', 'slug' => 'electrical', 'is_active' => true]);
        $this->actingAs($user)->get('/profile/edit')->assertRedirect('/provider/profile/edit');
        $this->put('/provider/profile', ['name' => 'Updated Provider', 'email' => 'provider@example.com', 'phone' => '0771234567', 'service_area' => 'Galle', 'category_ids' => [$category->id], 'is_available' => 1])->assertRedirect('/provider/dashboard');
        $this->assertSame('Updated Provider', $user->fresh()->name);
        $this->assertDatabaseHas('provider_profiles', ['user_id' => $user->id, 'service_area' => 'Galle']);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->get('/provider/profile/edit')->assertRedirect('/login');
        $this->assertGuest();
    }
}
