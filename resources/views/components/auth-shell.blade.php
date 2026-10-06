@props(['mode' => 'login'])
<div class="auth-experience auth-experience-{{ $mode }}">
<aside class="auth-story" aria-label="HomeServices account">
<div class="auth-story-brand"><span aria-hidden="true">⌂</span> Home<span>Services</span></div>
<p class="auth-story-kicker">A LITTLE HELP. A BETTER HOME.</p>
<div class="auth-house-art" aria-hidden="true">
<svg viewBox="0 0 360 290" fill="none"><circle cx="180" cy="146" r="119" fill="#ffffff" fill-opacity=".04"/><circle cx="180" cy="146" r="95" stroke="#ffffff" stroke-opacity=".12" stroke-dasharray="4 8"/>
<path d="M76 143L180 61l104 82" stroke="#a5e4dc" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/><path d="M95 135v117h170V135" fill="#f8f9f6"/><path d="M159 252v-69a21 21 0 0 1 42 0v69" fill="#087f82"/><rect x="115" y="157" width="28" height="31" rx="5" fill="#c8e8e1"/><rect x="218" y="157" width="28" height="31" rx="5" fill="#c8e8e1"/><path d="M129 157v31m89-16h28m-131 0h28m89-15v31" stroke="#087f82" stroke-width="2"/>
<rect x="230" y="44" width="67" height="67" rx="20" fill="#087f82"/><path d="m250 77 9 9 20-23" stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="80" cy="220" r="28" fill="#f4a361"/><path d="M80 207v26m-13-13h26" stroke="#123047" stroke-width="3" stroke-linecap="round"/><path d="M57 252h246" stroke="#ffffff" stroke-opacity=".25" stroke-width="2" stroke-linecap="round"/>
</svg><span class="auth-art-note">A little help, closer to home.</span>
</div>
<h2>{{ $mode === 'register' ? 'Your next chapter starts at home.' : ($mode === 'verify' ? 'One small step. A better connection.' : 'Make room for what matters.') }}</h2>
<p class="auth-story-copy">{{ $mode === 'register' ? 'Find help for your home, or bring your skills to the people who need them.' : ($mode === 'verify' ? 'Confirm your email once, then enjoy a simpler sign-in every time you come back.' : 'Your home, your services, your people. Pick up right where you left off.') }}</p>
<div class="auth-story-footer"><span class="auth-status-dot"></span> {{ $mode === 'verify' ? 'Your code stays private. Keep it that way.' : 'A thoughtful space for everyday help.' }}</div>
</aside>
<div class="auth-form-panel">{{ $slot }}</div>
</div>
