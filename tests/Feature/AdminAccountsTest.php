<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AdminAccountsTest extends TestCase
{
    use RefreshDatabase;
    public function test_admin_can_suspend_and_reactivate_provider_and_hide_listing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $provider = User::factory()->create(['role' => 'provider']);
        $provider->providerProfile()->create(['phone' => '0771234567', 'service_area' => 'Galle']);
        $category = ServiceCategory::create(['name' => 'Electrical', 'slug' => 'electrical', 'is_active' => true]);
        $provider->serviceCategories()->attach($category);
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()->assertSee($provider->email);
        $this->patch(route('admin.accounts.update', $provider), ['action' => 'suspend'])->assertRedirect();
        $this->assertNotNull($provider->fresh()->suspended_at);
        $this->get('/services/electrical')->assertDontSee($provider->name);
        $this->get(route('providers.reviews', $provider))->assertNotFound();
        $this->patch(route('admin.accounts.update', $provider), ['action' => 'reactivate'])->assertRedirect();
        $this->assertNull($provider->fresh()->suspended_at);
        $this->get('/services/electrical')->assertSee($provider->name);
        $this->patch(route('admin.accounts.update', $admin), ['action' => 'suspend'])->assertForbidden();
    }
    public function test_other_roles_cannot_manage_accounts_and_suspended_sessions_are_revoked(): void
    {
        $customer = User::factory()->create();
        $target = User::factory()->create();
        $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();
        $this->patch(route('admin.accounts.update', $target), ['action' => 'suspend'])->assertForbidden();
        $customer->forceFill(['suspended_at' => now()])->save();
        $this->get('/customer/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
