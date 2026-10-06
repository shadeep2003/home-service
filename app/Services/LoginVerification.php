<?php
namespace App\Services;
use App\Mail\LoginOtp;
use App\Models\LoginChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoginVerification
{
    public function start(Request $request, User $user): bool
    {
        // A duplicate credential submission must not discard an emailed code and
        // then fail to replace it because of the account-wide delivery cooldown.
        $pending = $this->current($request);
        if ($pending && (string) $pending->user_id === (string) $user->id && $pending->otp_hash
            && $pending->attempts < 5 && $pending->expires_at->gt(now())) {
            return true;
        }
        $this->cancel($request);
        $request->session()->regenerate();
        $binding = bin2hex(random_bytes(32));
        $challenge = LoginChallenge::create([
            'id' => (string) Str::uuid(), 'user_id' => $user->id,
            'binding_hash' => hash('sha256', $binding), 'account_hash' => $this->accountHash($user),
            'expires_at' => now(), 'resend_at' => now(), 'pending_until' => now()->addMinutes(15),
        ]);
        $request->session()->put('pending_login', ['id' => $challenge->id, 'binding' => $binding]);
        return DB::transaction(function () use ($challenge, $user) {
            $locked = LoginChallenge::whereKey($challenge->id)->lockForUpdate()->firstOrFail();
            return $this->deliver($locked, $user);
        });
    }

    public function current(Request $request, bool $lock = false): ?LoginChallenge
    {
        $pending = $request->session()->get('pending_login');
        if (! is_array($pending) || ! isset($pending['id'], $pending['binding'])) { return null; }
        $query = LoginChallenge::whereKey($pending['id']);
        $challenge = ($lock ? $query->lockForUpdate() : $query)->first();
        if (! $challenge || $challenge->consumed_at || $challenge->pending_until->lte(now())
            || ! hash_equals($challenge->binding_hash, hash('sha256', $pending['binding']))) { return null; }
        $user = User::find($challenge->user_id);
        if (! $user || $user->suspended_at || ! hash_equals($challenge->account_hash, $this->accountHash($user))) { return null; }
        return $challenge;
    }

    public function verify(Request $request, string $code): array
    {
        return DB::transaction(function () use ($request, $code) {
            $challenge = $this->current($request, true);
            if (! $challenge) { return ['error' => 'Your login request has ended. Return to login to start again.']; }
            if ($challenge->attempts >= 5) { return ['error' => 'Attempt limit reached. Return to login to start again.']; }
            if (! $challenge->otp_hash) { return ['error' => 'The email could not be sent. Request a new code or return to login.']; }
            if ($challenge->expires_at->lte(now())) { return ['error' => 'This code has expired. Request a new code.']; }
            if (! Hash::check($code, $challenge->otp_hash)) {
                $challenge->attempts++;
                if ($challenge->attempts >= 5) { $challenge->otp_hash = null; }
                $challenge->save();
                return ['error' => $challenge->attempts >= 5
                    ? 'Attempt limit reached. Return to login to start again.'
                    : 'Invalid code. '.(5 - $challenge->attempts).' attempts remaining.'];
            }
            // Consume under the same row lock before any session is authenticated.
            $challenge->forceFill(['otp_hash' => null, 'consumed_at' => now()])->save();
            $user = User::whereKey($challenge->user_id)->lockForUpdate()->firstOrFail();
            if ($user->suspended_at || ! hash_equals($challenge->account_hash, $this->accountHash($user))) {
                return ['error' => 'Your login request has ended. Return to login to start again.'];
            }
            if (! $user->email_verified_at) { $user->forceFill(['email_verified_at' => now()])->save(); }
            return ['user' => $user];
        });
    }

    public function resend(Request $request): array
    {
        return DB::transaction(function () use ($request) {
            $challenge = $this->current($request, true);
            if (! $challenge) { return ['error' => 'Your login request has ended. Return to login to start again.']; }
            if ($challenge->attempts >= 5) { return ['error' => 'Attempt limit reached. Return to login to start again.']; }
            if ($challenge->resend_at->gt(now())) { return ['error' => 'Please wait until the resend countdown ends.']; }
            return $this->deliver($challenge, User::findOrFail($challenge->user_id))
                ? ['status' => 'A new code has been sent. The previous code no longer works.']
                : ['error' => 'We could not send the email. Please retry after the countdown or return to login.'];
        });
    }

    private function deliver(LoginChallenge $challenge, User $user): bool
    {
        $previousHash = $challenge->otp_hash;
        $invalidate = function () use ($challenge): void {
            $challenge->forceFill(['otp_hash' => null, 'expires_at' => now(), 'resend_at' => now()->addSeconds(60)])->save();
        };
        try {
            if (config('mail.default') !== 'smtp') {
                Log::warning('Login OTP delivery blocked.', ['reason' => 'smtp_mailer_required']);
                $invalidate();
                return false;
            }
            $required = [
                'MAIL_HOST' => 'mail.mailers.smtp.host',
                'MAIL_USERNAME' => 'mail.mailers.smtp.username',
                'MAIL_PASSWORD' => 'mail.mailers.smtp.password',
                'MAIL_FROM_ADDRESS' => 'mail.from.address',
            ];
            $missing = array_keys(array_filter($required, fn ($key) => ! config($key)));
            if ($missing) {
                // Setting names only: never log their values, recipients, or challenge IDs.
                Log::warning('Login OTP delivery blocked.', ['reason' => 'smtp_configuration_missing', 'missing_settings' => $missing]);
                $invalidate();
                return false;
            }
            // Also enforce the cooldown across new challenges, IPs, and browser sessions.
            return Cache::lock('otp-delivery:'.$user->id, 30)->block(3, function () use ($challenge, $user, $previousHash) {
                $cooldown = 'otp-delivery-minute:'.$user->id;
                $hourly = 'otp-delivery-hour:'.$user->id;
                $minuteLimited = RateLimiter::tooManyAttempts($cooldown, 1);
                $hourLimited = RateLimiter::tooManyAttempts($hourly, 10);
                if ($minuteLimited || $hourLimited) {
                    Log::notice('Login OTP delivery blocked.', ['reason' => 'delivery_rate_limited']);
                    // No replacement was attempted: retain the already delivered code.
                    $wait = max($minuteLimited ? RateLimiter::availableIn($cooldown) : 0, $hourLimited ? RateLimiter::availableIn($hourly) : 0, 1);
                    $challenge->forceFill(['resend_at' => now()->addSeconds($wait)])->save();
                    return false;
                }
                RateLimiter::hit($cooldown, 60);
                RateLimiter::hit($hourly, 3600);
                do {
                    $code = sprintf('%06d', random_int(0, 999999));
                } while ($previousHash && Hash::check($code, $previousHash));
                $hash = Hash::make($code);
                // Save under the existing transaction/row lock before sending.
                // Verification cannot read the replacement until the transaction commits.
                $challenge->forceFill(['otp_hash' => $hash, 'expires_at' => now()->addMinutes(5), 'resend_at' => now()->addSeconds(60)])->save();
                Mail::to($user->email)->send(new LoginOtp($code));
                return true;
            });
        } catch (\Throwable $exception) {
            // Raw exceptions can contain credentials or message bodies; log only a fixed label.
            Log::warning('Login OTP delivery failed.', ['reason' => MailDeliveryFailure::reason($exception)]);
            $invalidate();
            return false;
        }
    }

    private function accountHash(User $user): string
    {
        return hash_hmac('sha256', $user->email.'|'.$user->password, config('app.key'));
    }

    public function cancel(Request $request): void
    {
        DB::transaction(function () use ($request) {
            if ($challenge = $this->current($request, true)) {
                $challenge->forceFill(['otp_hash' => null, 'consumed_at' => now()])->save();
            }
        });
        $request->session()->forget('pending_login');
    }
}
