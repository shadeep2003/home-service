<!DOCTYPE html>
<html lang="en" class="dashboard-page">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Your HomeServices workspace.">
    <title>@yield('title', 'Dashboard') · Home Services</title>
    <script>
        // Apply the stored desktop width before CSS paints; storage may be unavailable.
        try { document.documentElement.dataset.sidebarCollapsed = localStorage.getItem('homeservices.sidebar.collapsed') === 'true' ? 'true' : 'false'; } catch (error) {}
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="{{ asset('js/dashboard-navigation.js') }}" defer></script>
</head>
<body class="dashboard-body">
<a class="skip-link" href="#main">Skip to content</a>
<x-dashboard.sidebar :user="auth()->user()" />
<button class="dashboard-backdrop" type="button" aria-label="Close workspace navigation" tabindex="-1" data-dashboard-backdrop hidden></button>
<div class="dashboard-main" data-dashboard-main>
    <header class="dashboard-toolbar">
        <div class="dashboard-toolbar__heading">
            <button class="dashboard-desktop-toggle" type="button" aria-label="Collapse sidebar" title="Collapse sidebar" aria-expanded="true" aria-controls="dashboard-sidebar" data-dashboard-collapse><x-icon name="sidebar" /></button>
            <button class="dashboard-mobile-toggle" type="button" aria-label="Open workspace navigation" aria-expanded="false" aria-controls="dashboard-sidebar" data-dashboard-open><x-icon name="menu" /></button>
            <div><p class="dashboard-toolbar__eyebrow">YOUR WORKSPACE</p><p class="dashboard-toolbar__title">@yield('title', 'Dashboard')</p></div>
        </div>
        <a class="dashboard-website-link" href="{{ route('home') }}" aria-label="Visit the HomeServices website" title="Visit the HomeServices website"><span>Visit website</span><x-icon name="external" /></a>
    </header>
    <main id="main" class="dashboard-content" tabindex="-1">@yield('content')</main>
    <footer class="dashboard-footer"><span>HomeServices <span aria-hidden="true">·</span> A little help. A better home.</span><a href="{{ route('contact') }}">Need help?</a></footer>
</div>
</body>
</html>
