<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Resulty')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/landing.js'])
</head>
<body class="landing-page bg-white text-stone-900 antialiased">
    <header class="landing-header">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6">
            <a href="{{ route('home') }}" class="landing-logo">Resulty</a>
            <nav class="flex items-center gap-3 sm:gap-4">
                <a href="#how-it-works" class="landing-nav-link hidden sm:inline">How it works</a>
                <a href="{{ route('results') }}" class="landing-btn-glow">Process results</a>
            </nav>
        </div>
    </header>

    @yield('content')

    <footer class="landing-footer py-10">
        <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
            <p class="text-sm text-stone-500">Resulty · Composite result calculation for universities</p>
        </div>
    </footer>
</body>
</html>
