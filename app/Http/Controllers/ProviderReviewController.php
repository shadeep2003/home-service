<?php
namespace App\Http\Controllers;
use App\Enums\Role;
use App\Models\User;
use App\Models\ProviderReview;
use Illuminate\Http\Request;
class ProviderReviewController
{
    private function ensureProvider(User $provider): void
    {
        abort_unless($provider->role === Role::Provider && !$provider->suspended_at && $provider->providerProfile()->exists(), 404);
    }
    public function show(Request $request, User $provider)
    {
        $this->ensureProvider($provider);
        $provider->load('providerProfile')->loadCount('reviews')->loadAvg('reviews', 'rating');
        return view('public.provider-reviews', [
            'provider' => $provider,
            'reviews' => $provider->reviews()->with('customer')->latest()->paginate(10),
            'ownReview' => $request->user()?->role === Role::Customer
                ? $provider->reviews()->where('customer_id', $request->user()->id)->first() : null,
        ]);
    }
    public function store(Request $request, User $provider)
    {
        $this->ensureProvider($provider);
        abort_unless($request->user()->role === Role::Customer && $request->user()->id !== $provider->id, 403);
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1500'],
        ]);
        ProviderReview::upsert([
            ['provider_id' => $provider->id, 'customer_id' => $request->user()->id,
             'rating' => $data['rating'], 'comment' => $data['comment'] ?? null,
             'created_at' => now(), 'updated_at' => now()],
        ], ['provider_id', 'customer_id'], ['rating', 'comment', 'updated_at']);
        return redirect()->route('providers.reviews', $provider)->with('status', 'Your review has been saved.');
    }
}
