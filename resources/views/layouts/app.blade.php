<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ theme('meta_description') }}">

    <title>{{ theme('name') }} - @yield('title', theme('tagline'))</title>

    <!-- Favicon (theme-driven) -->
    <link rel="icon" type="image/png" href="{{ asset(theme('logo.favicon')) }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset(theme('logo.favicon')) }}">
    <link rel="apple-touch-icon" href="{{ asset(theme('logo.favicon')) }}">

    <!-- Google Fonts (theme-driven) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ theme('fonts.google_url') }}" rel="stylesheet">

    <!-- Tailwind CSS (CDN for development) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ theme('colors.primary') }}',
                        secondary: '{{ theme('colors.secondary') }}',
                        accent: '{{ theme('colors.accent') }}',
                        danger: '{{ theme('colors.danger') }}',
                        'dark-color': '{{ theme('colors.dark') }}',
                        'light-color': '{{ theme('colors.light') }}',
                        'sky-color': '{{ theme('colors.sky') }}',
                        'bg-primary': '{{ theme('colors.bg_primary') }}',
                        'bg-secondary': '{{ theme('colors.bg_secondary') }}',
                    },
                    fontFamily: {
                        heading: {!! json_encode([theme('fonts.heading')]) !!},
                        sans: {!! json_encode([theme('fonts.body')]) !!},
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @livewireStyles
    @yield('styles')

    <!-- Theme Stylesheets (theme-driven) -->
    @foreach(theme('css', []) as $cssFile)
        <link rel="stylesheet" href="{{ asset($cssFile) }}">
    @endforeach
</head>
<body class="font-sans antialiased {{ theme('body_class') }}">
    <!-- Header with Integrated Category Dropdown -->
    @include('layouts.header')

    <!-- Main Content -->
    <main class="min-h-screen pt-16 md:pt-20">
        @if(session('success'))
            <div class="container mx-auto px-4 mt-4">
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.parentElement.style.display='none'">
                        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container mx-auto px-4 mt-4">
                <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.parentElement.style.display='none'">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.footer')

    @livewireScripts
    <!-- Theme Scripts (theme-driven) -->
    @foreach(theme('js', []) as $jsFile)
        <script src="{{ asset($jsFile) }}"></script>
    @endforeach
    @yield('scripts')
</body>
</html>
