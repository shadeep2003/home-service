<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Mail::fake();
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.example.test',
            'mail.mailers.smtp.username' => 'test', 'mail.mailers.smtp.password' => 'test',
            'mail.from.address' => 'sender@example.test']);
    }
    public function test_customer_registration_hashes_password_and_requests_email_verification(): void
    {
        $this->post('/register', ['name' => 'Alex', 'email' => 'alex@example.test', 'role' => 'customer', 'password' => 'Password123', 'password_confirmation' => 'Password123'])->assertRedirect('/login/verify');
        $user = User::firstOrFail();
        $this->assertGuest();
        $this->assertTrue(Hash::check('Password123', $user->password));
        $this->assertSame('customer', $user->role->value);
        $this->assertNull($user->email_verified_at);
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\LoginOtp::class, fn ($mail) => $mail->hasTo($user->email));
    }
    public function test_public_registration_cannot_create_admin(): void
    {
        $this->post('/register', ['name' => 'Alex', 'email' => 'alex@example.test', 'role' => 'admin', 'password' => 'Password123', 'password_confirmation' => 'Password123'])->assertSessionHasErrors('role');
        $this->assertDatabaseCount('users', 0);
    }
    public function test_password_confirmation_is_required(): void
    {
        $this->post('/register', ['name' => 'Alex', 'email' => 'alex@example.test', 'role' => 'customer', 'password' => 'Password123', 'password_confirmation' => 'Different123'])->assertSessionHasErrors('password');
        $this->assertGuest();
    }
    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'alex@example.test']);
        $this->post('/register', ['name' => 'Alex', 'email' => 'alex@example.test', 'role' => 'provider', 'password' => 'Password123', 'password_confirmation' => 'Password123'])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }
    public function test_provider_registration_uses_provider_role(): void
    {
        $category = \App\Models\ServiceCategory::create(['name' => 'Electrical', 'slug' => 'electrical']);
        $this->post('/register', ['phone' => '0771234567', 'service_area' => 'Colombo', 'category_ids' => [$category->id], 'name' => 'Sam', 'email' => 'sam@example.test', 'role' => 'provider', 'password' => 'Password123', 'password_confirmation' => 'Password123'])->assertRedirect('/login/verify');
        $this->assertGuest();
        $this->get('/provider/dashboard')->assertRedirect('/login');
        $code = \Illuminate\Support\Facades\Mail::sent(\App\Mail\LoginOtp::class)->first()->code;
        $this->post('/login/verify', ['code' => $code])->assertRedirect('/provider/dashboard');
        $this->get('/dashboard')->assertRedirect('/provider/dashboard');
        $this->get('/provider/dashboard')->assertOk();
    }
    public function test_wrong_password_does_not_authenticate(): void
    {
        User::factory()->create(['email' => 'alex@example.test']);
        $this->post('/login', ['email' => 'alex@example.test', 'password' => 'WrongPassword'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
    public function test_role_dashboards_reject_other_roles_and_guests(): void
    {
        $this->get('/customer/dashboard')->assertRedirect('/login');
        foreach (['customer', 'provider', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach (['customer', 'provider', 'admin'] as $target) {
                $this->get('/'.$target.'/dashboard')->assertStatus($role === $target ? 200 : 403);
            }
        }
    }
    public function test_login_requests_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post('/login', ['email' => 'missing@example.test', 'password' => 'WrongPassword']);
        }
        $this->post('/login', ['email' => 'missing@example.test', 'password' => 'WrongPassword'])->assertStatus(429);
    }
}
