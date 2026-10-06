<?php
namespace Tests\Feature;
use App\Mail\LoginOtp;
use App\Models\LoginChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
class LoginOtpTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.example.test',
            'mail.mailers.smtp.username' => 'test', 'mail.mailers.smtp.password' => 'test',
            'mail.from.address' => 'sender@example.test']);
        Mail::fake();
    }
    private function start(?User $user = null): array
    {
        $user ??= User::factory()->unverified()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect('/login/verify');
        $this->assertGuest();
        $mail = Mail::sent(LoginOtp::class)->last();
        return [$user, $mail->code, LoginChallenge::latest()->firstOrFail()];
    }
    public function test_success_hash_storage_email_template_and_single_use(): void
    {
        [$user, $code, $challenge] = $this->start();
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
        $this->assertNotSame($code, $challenge->otp_hash);
        $this->assertTrue(Hash::check($code, $challenge->otp_hash));
        Mail::assertSent(LoginOtp::class, fn ($mail) => $mail->hasTo($user->email) && str_contains($mail->render(), '5 minutes'));
        $this->get('/login/verify')->assertOk()->assertSee('Verification code')
            ->assertSee('data-delivered="true"', false)->assertDontSee('Email delivery failed.');
        $this->post('/login/verify', ['code' => $code])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertNull($challenge->fresh()->otp_hash);
        $this->assertNotNull($challenge->fresh()->consumed_at);
        $this->get('/dashboard')->assertRedirect('/customer/dashboard');
        $this->get('/customer/dashboard')->assertOk()->assertSee('Email verified successfully.');
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->post('/login/verify', ['code' => $code])->assertRedirect('/login/verify');
        $this->assertGuest();
    }
    public function test_invalid_credentials_are_generic_and_do_not_send_mail(): void
    {
        $user = User::factory()->unverified()->create();
        $responses = [];
        foreach ([$user->email, 'missing@example.test'] as $email) {
            $this->post('/login', ['email' => $email, 'password' => 'incorrect'])->assertSessionHasErrors('email');
            $responses[] = session('errors')->first('email');
        }
        $this->assertSame($responses[0], $responses[1]);
        Mail::assertNothingSent();
        $this->assertDatabaseCount('login_challenges', 0);
        $this->assertGuest();
    }
    public function test_all_protected_paths_are_blocked_before_verification(): void
    {
        $this->start();
        foreach (['/dashboard', '/customer/dashboard', '/provider/dashboard', '/admin/dashboard', '/profile/edit'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
        $this->put('/profile', [])->assertRedirect('/login');
        $this->postJson('/profile/photo', [])->assertUnauthorized();
        $this->assertGuest();
    }
    public function test_five_incorrect_codes_lock_challenge_and_resend_cannot_reset_attempts(): void
    {
        [, $code, $challenge] = $this->start();
        $wrong = $code === '000000' ? '000001' : '000000';
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login/verify', ['code' => $wrong])->assertSessionHasErrors('code');
        }
        $this->assertSame(5, $challenge->fresh()->attempts);
        $this->travel(61)->seconds();
        $this->post('/login/verify/resend')->assertSessionHasErrors('code');
        $this->post('/login/verify', ['code' => $code])->assertSessionHasErrors('code');
        Mail::assertSentCount(1);
        $this->assertGuest();
    }
    public function test_expiry_and_resend_invalidates_old_code_without_resetting_attempts(): void
    {
        [, $old, $challenge] = $this->start();
        $this->post('/login/verify/resend')->assertSessionHasErrors('code');
        Mail::assertSentCount(1);
        $this->travel(5)->minutes();
        $this->post('/login/verify', ['code' => $old])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->post('/login/verify/resend')->assertSessionHas('status');
        $this->get('/login/verify')->assertOk()->assertSee('data-delivered="true"', false)->assertDontSee('Email delivery failed.');
        $new = Mail::sent(LoginOtp::class)->last()->code;
        $this->assertFalse(Hash::check($old, $challenge->fresh()->otp_hash));
        $this->post('/login/verify', ['code' => $old])->assertSessionHasErrors('code');
        $this->post('/login/verify', ['code' => $new])->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }
    public function test_verification_and_resend_requests_are_rate_limited(): void
    {
        $this->start();
        for ($i = 0; $i < 10; $i++) { $this->post('/login/verify', ['code' => 'bad']); }
        $this->post('/login/verify', ['code' => 'bad'])->assertStatus(429);
        for ($i = 0; $i < 3; $i++) { $this->post('/login/verify/resend'); }
        $this->post('/login/verify/resend')->assertStatus(429);
        $this->assertGuest();
    }
    public function test_challenge_cannot_be_used_from_another_session_or_user(): void
    {
        [, $code, $challenge] = $this->start();
        $pending = session('pending_login');
        $this->withSession(['pending_login' => ['id' => $challenge->id, 'binding' => str_repeat('0', 64)]]);
        $this->post('/login/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
        $other = User::factory()->unverified()->create();
        [, $otherCode] = $this->start($other);
        $this->post('/login/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->post('/login/verify', ['code' => $otherCode])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($other);
    }
    public function test_refresh_expired_pending_and_cancel_are_safe(): void
    {
        [, $code, $challenge] = $this->start();
        $this->get('/login/verify')->assertOk();
        $this->get('/login/verify')->assertOk();
        Mail::assertSentCount(1);
        $this->post('/login/verify/cancel')->assertRedirect('/login');
        $this->assertNotNull($challenge->fresh()->consumed_at);
        $this->post('/login/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->travel(61)->seconds();
        $this->start();
        $this->travel(15)->minutes();
        $this->get('/login/verify')->assertRedirect('/login');
        $this->post('/login/verify/resend')->assertSessionHasErrors('code');
        $this->assertGuest();
    }
    public function test_delivery_failures_and_missing_settings_never_authenticate(): void
    {
        config(['mail.mailers.smtp.password' => null]);
        $user = User::factory()->unverified()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])
            ->assertRedirect('/login/verify')->assertSessionHasErrors('code');
        Mail::assertNothingSent();
        $this->assertNull(LoginChallenge::first()->otp_hash);
        $this->assertGuest();
        config(['mail.mailers.smtp.password' => 'test']);
        $this->travel(61)->seconds();
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Delivery failed'));
        $this->post('/login/verify/resend')->assertSessionHasErrors('code');
        $this->assertNull(LoginChallenge::first()->otp_hash);
        $this->assertGuest();
    }
    public function test_resend_failure_invalidates_previous_code(): void
    {
        [, $code, $challenge] = $this->start();
        $this->travel(61)->seconds();
        config(['mail.mailers.smtp.password' => null]);
        $this->post('/login/verify/resend')->assertSessionHasErrors('code');
        $this->assertNull($challenge->fresh()->otp_hash);
        $this->post('/login/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }
    public function test_account_changes_invalidate_unverified_pending_login(): void
    {
        [$user, $code] = $this->start();
        $user->forceFill(['suspended_at' => now()])->save();
        $this->post('/login/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
        $user->forceFill(['suspended_at' => null, 'email_verified_at' => null])->save();
        $this->travel(61)->seconds();
        [, $code] = $this->start($user);
        $this->get('/dashboard')->assertRedirect('/login');
        $user->forceFill(['password' => 'DifferentPassword123'])->save();
        $this->post('/login/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }
    public function test_provider_and_admin_are_redirected_to_their_dashboards(): void
    {
        foreach (['provider', 'admin'] as $role) {
            [$user, $code] = $this->start(User::factory()->unverified()->create(['role' => $role]));
            $this->post('/login/verify', ['code' => $code])->assertRedirect('/dashboard');
            $this->get('/dashboard')->assertRedirect('/'.$role.'/dashboard');
            $this->post('/logout');
        }
    }
    public function test_duplicate_login_retains_the_delivered_challenge_during_cooldown(): void
    {
        [$user, $code, $challenge] = $this->start();
        $pending = session('pending_login');
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])
            ->assertRedirect('/login/verify')->assertSessionDoesntHaveErrors();
        $this->assertSame($pending, session('pending_login'));
        $this->assertNull($challenge->fresh()->consumed_at);
        $this->assertTrue(Hash::check($code, $challenge->fresh()->otp_hash));
        Mail::assertSentCount(1);
        $this->get('/login/verify')->assertOk()->assertSee('data-delivered="true"', false)->assertDontSee('Email delivery failed.');
        $this->post('/login/verify', ['code' => $code])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
    public function test_account_login_limit_applies_across_different_ip_addresses(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.($i + 1)])
                ->post('/login', ['email' => 'missing@example.test', 'password' => 'wrong']);
        }
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.20'])
            ->post('/login', ['email' => 'MISSING@example.test', 'password' => 'wrong'])->assertStatus(429);
    }
    public function test_email_change_clears_verified_status_and_invalidates_pending_challenge(): void
    {
        [$user, $code] = $this->start();
        $user->forceFill(['email_verified_at' => now()])->save();
        \App\Services\AccountProfile::save($user, ['email' => 'new@example.test']);
        $this->assertNull($user->fresh()->email_verified_at);
        $this->post('/login/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_otp_is_never_flashed_and_non_string_codes_are_rejected_safely(): void
    {
        $this->start();
        $this->post('/login/verify', ['code' => ['123456']])->assertSessionHasErrors('code')->assertSessionMissing('_old_input.code');
        $this->post('/login/verify', ['code' => '123'])->assertSessionHasErrors('code')->assertSessionMissing('_old_input.code');
        $this->assertGuest();
    }
    public function test_non_delivery_mailers_are_rejected_without_sending_or_logging_codes(): void
    {
        config(['mail.default' => 'log']);
        $user = User::factory()->unverified()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertSessionHasErrors('code');
        Mail::assertNothingSent();
        $this->assertNull(LoginChallenge::first()->otp_hash);
        $this->assertGuest();
    }

    public function test_migration_recovers_when_email_column_exists_after_partial_mysql_ddl(): void
    {
        \Illuminate\Support\Facades\Schema::drop('login_challenges');
        $migration = require database_path('migrations/2026_10_06_000001_create_login_challenges.php');
        $migration->up();
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('login_challenges'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_verified_at'));
        $this->start();
    }

    public function test_missing_mail_settings_are_diagnosed_using_names_only(): void
    {
        \Illuminate\Support\Facades\Log::spy();
        config(['mail.mailers.smtp.password' => null]);
        $user = User::factory()->unverified()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertSessionHasErrors('code');
        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->once()->with('Login OTP delivery blocked.', [
            'reason' => 'smtp_configuration_missing', 'missing_settings' => ['MAIL_PASSWORD'],
        ]);
        Mail::assertNothingSent();
        $this->assertGuest();
    }
    public function test_delivery_exception_is_classified_without_logging_its_contents(): void
    {
        \Illuminate\Support\Facades\Log::spy();
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Failed to authenticate: private diagnostic content must not be logged'));
        $user = User::factory()->unverified()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertSessionHasErrors('code');
        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->once()->with('Login OTP delivery failed.', [
            'reason' => 'smtp_authentication_rejected',
        ]);
        $this->assertNull(LoginChallenge::first()->otp_hash);
        $this->assertGuest();
    }

    public function test_rate_limited_resend_preserves_the_current_delivered_code(): void
    {
        [$user, $code, $challenge] = $this->start();
        $this->travel(61)->seconds();
        \Illuminate\Support\Facades\RateLimiter::hit('otp-delivery-minute:'.$user->id, 60);
        $this->post('/login/verify/resend')->assertSessionHasErrors('code');
        $this->assertTrue(Hash::check($code, $challenge->fresh()->otp_hash));
        $this->get('/login/verify')->assertOk()->assertSee('data-delivered="true"', false)->assertDontSee('Email delivery failed.');
        $this->post('/login/verify', ['code' => $code])->assertRedirect('/dashboard');
        Mail::assertSentCount(1);
    }
    public function test_hash_is_persisted_before_the_mail_send_method_is_called(): void
    {
        $user = User::factory()->unverified()->create();
        Mail::shouldReceive('to')->once()->with($user->email)->andReturnSelf();
        Mail::shouldReceive('send')->once()->with(\Mockery::on(function ($mail) {
            $challenge = LoginChallenge::firstOrFail();
            return $mail instanceof LoginOtp && $challenge->otp_hash
                && Hash::check($mail->code, $challenge->otp_hash) && $challenge->expires_at->gt(now());
        }));
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertSessionDoesntHaveErrors();
        $this->assertNotNull(LoginChallenge::first()->otp_hash);
    }

    public function test_successful_resend_removes_stale_delivery_errors(): void
    {
        config(['mail.mailers.smtp.password' => null]);
        $user = User::factory()->unverified()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertSessionHasErrors('code');
        config(['mail.mailers.smtp.password' => 'test']);
        $this->travel(61)->seconds();
        $this->post('/login/verify/resend')->assertSessionHas('status')->assertSessionDoesntHaveErrors();
        $this->get('/login/verify')->assertOk()->assertSee('data-delivered="true"', false)->assertDontSee('Email delivery failed.');
        $code = Mail::sent(LoginOtp::class)->last()->code;
        $this->post('/login/verify', ['code' => $code])->assertRedirect('/dashboard');
    }
    public function test_storage_failure_before_sending_does_not_email_an_unusable_code(): void
    {
        $fail = true;
        LoginChallenge::updating(function ($challenge) use (&$fail) {
            if ($fail && $challenge->isDirty('otp_hash') && $challenge->otp_hash) {
                throw new \RuntimeException('Simulated challenge write failure');
            }
        });
        try {
            $user = User::factory()->unverified()->create();
            $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertSessionHasErrors('code');
            Mail::assertNothingSent();
            $this->assertNull(LoginChallenge::first()->otp_hash);
            $this->assertGuest();
        } finally { $fail = false; }
    }

}
