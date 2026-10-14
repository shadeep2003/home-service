@extends('layouts.app')
@section('title', 'Services')
@section('content')
<section class="catalogue-header">
    <p class="eyebrow"><span class="eyebrow-line" aria-hidden="true"></span>CARE FOR EVERY CORNER</p>
    <div class="catalogue-header__row"><div><h1>What does your home <span>need today?</span></h1><p class="lead">From everyday upkeep to a much-needed refresh, explore the skills that keep a home feeling like home.</p><p class="catalogue-header__guide">Choose a category, explore its services, and find a local professional.</p></div><span class="catalogue-header__count">{{ $categories->total() }} {{ $categories->total() === 1 ? 'service category' : 'service categories' }}</span></div>
</section>
<section class="catalogue-list" aria-label="Service categories"><x-service-grid :categories="$categories" /><div class="catalogue-pagination">{{ $categories->links() }}</div></section>
<x-how-it-works /><x-cta />
@endsection
