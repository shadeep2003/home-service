<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="HomeServices connects householders with home service professionals. Explore our public platform preview.">
    <title>@yield('title', 'Home Services') · Home Services</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/navigation.js') }}" defer></script>
</head>
<body>
<x-navbar />
<main id="main" class="container">@yield('content')</main>
<x-footer />
</body>
</html>
