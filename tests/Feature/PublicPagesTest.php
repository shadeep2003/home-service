<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;
    public function test_public_pages_and_auth_forms_render_with_database_categories(): void
    {
        foreach (['/' => 'Expert Home Services,', '/services' => 'What does your home', '/about' => 'Better connections.', '/contact' => 'A little guidance.', '/login' => 'Your home. Your people.', '/register' => 'A little help starts here.'] as $url => $heading) {
            $response = $this->get($url);
            $response->assertOk()->assertSee($heading)->assertSee('Main navigation');
            foreach (['/services', '/about', '/contact', '/login', '/register'] as $link) {
                $response->assertSee('href="'.url($link).'"', false);
            }
        }
    }

    public function test_homepage_uses_real_categories_and_links_to_public_destinations(): void
    {
        $this->seed(\Database\Seeders\ServiceCategorySeeder::class);
        $this->get('/')
            ->assertOk()
            ->assertDontSee('DEMO PROFILE')
            ->assertDontSee('Placeholder testimonial')
            ->assertSee('id="how-it-works"', false)
            ->assertSee('href="'.route('register', ['role' => 'provider']).'"', false);

        foreach (['Electrical', 'Plumbing', 'Cleaning', 'Painting', 'AC Repair', 'Gardening'] as $category) {
            $this->get('/services')->assertSee($category);
        }
    }

    public function test_provider_signup_link_preserves_provider_selection(): void
    {
        $this->get('/register?role=provider')->assertOk()
            ->assertSee('value="provider" selected', false)
            ->assertSee('name="_token"', false);
    }
}
