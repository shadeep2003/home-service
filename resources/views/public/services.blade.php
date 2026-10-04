@extends('layouts.app')
@section('title', 'Services')
@section('content')
<section class="page-intro"><p class="eyebrow">CARE FOR EVERY CORNER</p><h1>What does your home <span>need today?</span></h1><p class="lead">From everyday upkeep to a much-needed refresh, explore the skills that keep a home feeling like home.</p></section><section class="section service-catalogue" aria-label="Service categories"><x-service-grid :categories="$categories" />{{ $categories->links() }}</section><x-how-it-works /><x-cta />
@endsection
