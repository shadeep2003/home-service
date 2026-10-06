<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_navigation_keeps_public_routes_and_registration_call_to_action(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('aria-label="Main navigation"', false)
            ->assertSee('href="'.route('services').'"', false)
            ->assertSee('href="'.route('about').'"', false)
            ->assertSee('href="'.route('contact').'"', false)
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('aria-controls="mobile-navigation"', false);
    }

    public function test_each_role_sees_only_its_account_navigation_links(): void
    {
        $expectedRoleRoutes = [
            'customer' => ['customer.dashboard', 'services'],
            'provider' => ['provider.dashboard', 'provider.profile.edit'],
            'admin' => ['admin.dashboard', 'admin.categories.index'],
        ];

        foreach ($expectedRoleRoutes as $role => $routes) {
            $user = User::factory()->create(['name' => 'Taylor Home', 'role' => $role]);

            $response = $this->actingAs($user)->get('/')->assertOk();
            $response->assertSee('Taylor Home');
            $response->assertSee('aria-label="Account menu for Taylor Home"', false);
            $response->assertSee('href="'.route($routes[0]).'"', false);
            $response->assertSee('href="'.route('profile.edit').'"', false);
            $response->assertSee('action="'.route('logout').'"', false);

            if ($role === 'customer') {
                $response->assertSee('href="'.route('services').'"', false);
                $response->assertDontSee('href="'.route('admin.dashboard').'"', false);
                $response->assertDontSee('href="'.route('provider.profile.edit').'"', false);
            } elseif ($role === 'provider') {
                $response->assertSee('href="'.route('provider.profile.edit').'"', false);
                $response->assertDontSee('href="'.route('admin.categories.index').'"', false);
            } else {
                $response->assertSee('href="'.route('admin.categories.index').'"', false);
                $response->assertDontSee('href="'.route('provider.profile.edit').'"', false);
            }

            auth()->logout();
        }
    }
}
