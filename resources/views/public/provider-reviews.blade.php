@extends('layouts.app')
@section('title', $provider->name.' · Reviews')
@section('content')
<section class="page-intro"><a href="{{ route('services') }}">← Browse services</a><x-profile-photo :user="$provider" /><p class="eyebrow">CUSTOMER FEEDBACK</p><h1>{{ $provider->name }}</h1><p class="lead">{{ $provider->providerProfile->service_area }}</p></section>
@if(session('status'))<p class="profile-save-notice" role="status">{{ session('status') }}</p>@endif
<div class="reviews-layout">
<section class="panel review-summary"><p class="eyebrow">SERVICE RATING</p><h2>@if($provider->reviews_count){{ number_format($provider->reviews_avg_rating, 1) }} <small>/ 5</small>@else No ratings yet @endif</h2><p>{{ $provider->reviews_count }} {{ $provider->reviews_count === 1 ? 'customer review' : 'customer reviews' }}</p><p class="muted">Feedback from customer accounts. Service completion has not been verified.</p>
@auth
@if(auth()->user()->role === \App\Enums\Role::Customer)
<form method="POST" action="{{ route('providers.reviews.store', $provider) }}">@csrf
<h3>{{ $ownReview ? 'Update your review' : 'Rate your experience' }}</h3><p class="muted">Share your experience after using this provider’s services.</p>
<fieldset class="rating-choice"><legend>Choose a rating</legend>@for($score = 1; $score <= 5; $score++)<label><input type="radio" name="rating" value="{{ $score }}" required @checked((string) old('rating', $ownReview?->rating) === (string) $score)><span>{{ $score }} <span aria-hidden="true">★</span></span><span class="rating-accessible">{{ $score === 1 ? 'star' : 'stars' }}</span></label>@endfor</fieldset>
@error('rating')<p class="field-error">{{ $message }}</p>@enderror
<div class="field"><label for="comment">Your review (optional)</label><textarea id="comment" name="comment" rows="4" maxlength="1500" placeholder="What went well? What could be improved?">{{ old('comment', $ownReview?->comment) }}</textarea>@error('comment')<p class="field-error">{{ $message }}</p>@enderror</div>
<p class="muted">Your name and review will be public. You can update your review here.</p><button class="button full">{{ $ownReview ? 'Update review' : 'Submit review' }}</button></form>
@else<p class="muted">Customer accounts can leave reviews.</p>@endif
@else<a class="button full" href="{{ route('login') }}">Log in to rate this provider</a>@endauth
</section>
<section class="review-list" aria-label="Customer reviews"><h2>Customer reviews</h2>
@forelse($reviews as $review)<article class="panel customer-review"><header><h3>{{ $review->customer->name }}</h3><span class="review-score"><span aria-hidden="true">★</span> {{ $review->rating }} / 5</span></header><p class="review-date">{{ $review->created_at->format('d M Y') }}@if($review->updated_at->gt($review->created_at)) · Updated {{ $review->updated_at->format('d M Y') }}@endif</p>@if($review->comment)<p class="review-comment">{{ $review->comment }}</p>@else<p class="muted">Rating only</p>@endif</article>
@empty<div class="panel"><h3>Be the first to share your experience</h3><p>No customer reviews yet.</p></div>@endforelse
{{ $reviews->links() }}
</section></div>
@endsection
