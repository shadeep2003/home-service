<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ProviderReviewsTest extends TestCase
{
    use RefreshDatabase;
    private function provider(): User
    {
        $provider = User::factory()->create(['role' => 'provider']);
        $provider->providerProfile()->create(['phone' => '0771234567', 'service_area' => 'Galle']);
        return $provider;
    }
    public function test_customer_can_submit_and_update_one_review_without_spoofing_author(): void
    {
        $provider = $this->provider(); $customer = User::factory()->create();
        $url = route('providers.reviews.store', $provider);
        $this->actingAs($customer)->post($url, ['rating' => 5, 'comment' => 'Great service', 'customer_id' => $provider->id])
            ->assertRedirect(route('providers.reviews', $provider));
        $this->assertDatabaseHas('provider_reviews', ['customer_id' => $customer->id, 'provider_id' => $provider->id, 'rating' => 5]);
        $this->post($url, ['rating' => 3, 'comment' => 'Updated feedback'])->assertRedirect();
        $this->assertDatabaseCount('provider_reviews', 1);
        $this->get(route('providers.reviews', $provider))->assertOk()->assertSee('3.0')->assertSee('Updated feedback')->assertSee('Update your review');
    }
    public function test_guest_and_non_customers_cannot_submit_reviews(): void
    {
        $provider = $this->provider(); $url = route('providers.reviews.store', $provider);
        $this->post($url, ['rating' => 5])->assertRedirect(route('login'));
        foreach (['provider', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->post($url, ['rating' => 5])->assertForbidden();
        }
        $this->assertDatabaseCount('provider_reviews', 0);
    }
    public function test_invalid_ratings_and_non_provider_targets_are_rejected(): void
    {
        $provider = $this->provider(); $this->actingAs(User::factory()->create());
        foreach ([0, 6, 2.5, 'bad'] as $rating) {
            $this->post(route('providers.reviews.store', $provider), ['rating' => $rating])->assertSessionHasErrors('rating');
        }
        $this->post(route('providers.reviews.store', $provider), ['rating' => 4, 'comment' => str_repeat('a', 1501)])->assertSessionHasErrors('comment');
        $customer = User::factory()->create();
        $this->post(route('providers.reviews.store', $customer), ['rating' => 4])->assertNotFound();
        $this->get(route('providers.reviews', $customer))->assertNotFound();
        $this->assertDatabaseCount('provider_reviews', 0);
    }
    public function test_real_averages_are_visible_and_review_text_is_escaped(): void
    {
        $provider = $this->provider();
        $category = ServiceCategory::create(['name' => 'Electrical', 'slug' => 'electrical', 'is_active' => true]);
        $provider->serviceCategories()->attach($category);
        foreach ([5, 3] as $rating) {
            $this->actingAs(User::factory()->create())->post(route('providers.reviews.store', $provider), ['rating' => $rating, 'comment' => '<script>alert(1)</script>'])->assertRedirect();
        }
        $this->get('/services/electrical')->assertOk()->assertSee('4.0 / 5')->assertSee('(2 reviews)');
        $this->get(route('providers.reviews', $provider))->assertOk()->assertSee('<script>alert(1)</script>')->assertDontSee('<script>alert(1)</script>', false);
    }
}
