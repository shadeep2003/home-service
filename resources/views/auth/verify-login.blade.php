@extends('layouts.app')
@section('title', 'Verify email')
@section('content')
<x-auth-shell mode="verify">
<section class="auth-form" id="login-verification" data-expires="{{ $challenge->expires_at->getTimestamp() }}" data-resend="{{ $challenge->resend_at->getTimestamp() }}" data-server-time="{{ now()->getTimestamp() }}" data-locked="{{ $challenge->attempts >= 5 ? 'true' : 'false' }}" data-delivered="{{ $challenge->otp_hash ? 'true' : 'false' }}">
<p class="eyebrow">ONE MORE STEP</p><h1>Check your email</h1>
<p class="muted">Enter the six-digit verification code sent to your email address to activate your HomeServices account.</p>
@if(session('status'))<p role="status" class="otp-status">{{ session('status') }}</p>@endif
@error('code')<p role="alert" class="field-error" id="code-error">{{ $message }}</p>@enderror
@if($challenge->attempts >= 5)<p role="alert">Attempt limit reached. Return to login to start again.</p>
@elseif(! $challenge->otp_hash)<p role="alert">Email delivery failed. Please retry after the countdown, or return to login.</p>@endif
<form method="POST" action="{{ route('login.verify.submit') }}" data-loading="Verifying…">@csrf
<div class="field"><label for="code">Verification code</label>
<input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required autofocus class="otp-input" aria-describedby="code-help expiry @error('code') code-error @enderror">
<p id="code-help" class="muted">Type or paste all six digits. Use Tab to move between controls.</p></div>
<p id="expiry" role="status" class="muted">{{ $challenge->expires_at->lte(now()) ? 'Code expired. Request a new code.' : 'Your code expires in 5 minutes.' }}</p>
<button class="button full" id="verify-button" type="submit" @disabled($challenge->attempts >= 5 || ! $challenge->otp_hash || $challenge->expires_at->lte(now()))>Verify email and continue</button>
</form>
<form method="POST" action="{{ route('login.verify.resend') }}" data-loading="Sending…">@csrf
<p id="resend-countdown" class="muted">You can request another code after the 60-second cooldown.</p>
<button class="button secondary full" id="resend-button" type="submit" @disabled($challenge->attempts >= 5)>Resend code</button></form>
<form method="POST" action="{{ route('login.verify.cancel') }}" data-loading="Returning…">@csrf
<button class="auth-text-button" type="submit">← Return to login</button></form>
<noscript><p>Countdowns require JavaScript. The server still enforces expiry and resend limits.</p></noscript>
</section>
</x-auth-shell>
<script src="{{ asset('js/login-verification.js') }}" defer></script>
@endsection
