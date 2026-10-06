<?php
namespace Tests\Feature;
use App\Mail\LoginOtp;
use App\Models\LoginChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.example.test',
            'mail.mailers.smtp.username' => 'test', 'mail.mailers.smtp.password' => 'test',
            'mail.from.address' => 'sender@example.test']);
    }
    public function test_registration_verifies_once_then_future_password_login_sends_no_code(): void
    {
        $this->post('/register', ['name' => 'Alex', 'email' => 'alex@example.test', 'role' => 'customer',
            'password' => 'Password123', 'password_confirmation' => 'Password123', 'email_verified_at' => now()->toDateTimeString()])
            ->assertRedirect('/login/verify');
        $user = User::firstOrFail();
        $this->assertNull($user->email_verified_at);
        $this->assertGuest();
        Mail::assertSentCount(1);
        $code = Mail::sent(LoginOtp::class)->first()->code;
        $this->post('/login/verify', ['code' => $code])->assertRedirect('/dashboard');
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertRedirect('/customer/dashboard');
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        // Verified password logins do not depend on email delivery availability.
        config(['mail.mailers.smtp.password' => null]);
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('login_challenges', 1);
        Mail::assertSentCount(1);
        $this->assertNull(LoginChallenge::first()->otp_hash);
    }
    public function test_unverified_existing_user_requires_verification_and_wrong_or_expired_code_does_not_verify(): void
    {
        $user = User::factory()->unverified()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect('/login/verify');
        $code = Mail::sent(LoginOtp::class)->first()->code;
        $wrong = $code === '000000' ? '000001' : '000000';
        $this->post('/login/verify', ['code' => $wrong])->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->travel(5)->minutes();
        $this->post('/login/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertGuest();
    }
    public function test_legacy_authenticated_unverified_sessions_cannot_bypass_verification(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get('/customer/dashboard')->assertRedirect('/login');
        $this->assertGuest();
        $this->actingAs($user)->postJson('/profile/photo', [])->assertForbidden();
        $this->assertGuest();
    }
    public function test_verified_password_login_preserves_role_dashboards_and_sends_no_mail(): void
    {
        foreach (['customer', 'provider', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect('/dashboard');
            $this->get('/dashboard')->assertRedirect('/'.$role.'/dashboard');
            $this->assertAuthenticatedAs($user);
            $this->post('/logout')->assertRedirect('/');
        }
        Mail::assertNothingSent();
        $this->assertDatabaseCount('login_challenges', 0);
    }
    public function test_email_change_requires_new_address_verification_before_access(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put('/profile', ['name' => $user->name, 'email' => 'new@example.test'])->assertRedirect('/profile/edit');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->get('/customer/dashboard')->assertRedirect('/login');
        $this->assertGuest();
        $this->post('/login', ['email' => 'new@example.test', 'password' => 'Password123'])->assertRedirect('/login/verify');
        Mail::assertSent(LoginOtp::class, fn ($mail) => $mail->hasTo('new@example.test'));
        $code = Mail::sent(LoginOtp::class)->first()->code;
        $this->post('/login/verify', ['code' => $code])->assertRedirect('/dashboard');
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }
    public function test_profile_update_without_email_change_keeps_verification(): void
    {
        $user = User::factory()->create();
        $verifiedAt = $user->email_verified_at->toDateTimeString();
        $this->actingAs($user)->put('/profile', ['name' => 'Updated', 'email' => $user->email])->assertRedirect('/profile/edit');
        $this->assertSame($verifiedAt, $user->fresh()->email_verified_at->toDateTimeString());
        $this->get('/customer/dashboard')->assertOk();
        Mail::assertNothingSent();
    }
    public function test_rejected_duplicate_email_does_not_clear_verified_status(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $verifiedAt = $user->email_verified_at->toDateTimeString();
        $this->actingAs($user)->put('/profile', ['name' => $user->name, 'email' => $other->email])->assertSessionHasErrors('email');
        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertSame($verifiedAt, $user->fresh()->email_verified_at->toDateTimeString());
        $this->get('/customer/dashboard')->assertOk();
    }

}
