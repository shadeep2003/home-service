<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PaginationChartsTest extends TestCase
{
    use RefreshDatabase;
    public function test_services_are_paginated_with_shared_controls(): void
    {
        for ($i = 1; $i <= 13; $i++) {
            ServiceCategory::create(['name' => sprintf('Service %02d', $i), 'slug' => 'service-'.$i, 'is_active' => true]);
        }
        $this->get('/services')->assertOk()->assertSee('Service 01')->assertDontSee('Service 13')->assertSee('Pagination')->assertSee('Next →');
        $this->get('/services?page=2')->assertOk()->assertSee('Service 13')->assertDontSee('Service 01')->assertSee('← Previous');
    }
    public function test_admin_charts_count_accounts_and_pagination_keeps_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(13)->create(['role' => 'customer']);
        $provider = User::factory()->create(['role' => 'provider']);
        $provider->providerProfile()->create(['phone' => '0771234567', 'service_area' => 'Galle', 'is_working' => true]);
        $response = $this->actingAs($admin)->get('/admin/dashboard?role=customer');
        $response->assertOk()->assertSee('Account types')->assertSee('Account status')->assertSee('Active provider availability')->assertSee('role=customer&amp;page=2', false);
        $charts = $response->viewData('charts');
        $this->assertSame(13, $charts['Account types'][0]['value']);
        $this->assertSame(1, $charts['Account types'][1]['value']);
        $this->assertSame(1, $charts['Active provider availability'][1]['value']);
        $this->get('/admin/dashboard?role=customer&page=2')->assertOk()->assertSee('Pagination');
    }
}
