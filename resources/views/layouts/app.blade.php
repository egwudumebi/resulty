<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Resulty')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-stone-100 text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-4 py-5 sm:px-6">
            <div>
                <a href="{{ route('home') }}" class="text-xl font-semibold tracking-tight text-stone-900 hover:text-stone-700">Resulty</a>
                <p class="mt-0.5 text-sm text-stone-500">Results workspace</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden items-center gap-6 text-sm sm:flex">
                    <div class="text-right">
                        <p class="text-stone-500">Grading scale</p>
                        <p class="font-medium text-stone-800">A=5 · B=4 · C=3 · D=2 · E=1</p>
                    </div>
                    <div class="h-8 w-px bg-stone-200"></div>
                    <div class="text-right">
                        <p class="text-stone-500">Output formats</p>
                        <p class="font-medium text-stone-800">Excel · Word (.docx)</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 sm:py-8">
        @if (session('error'))
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
