<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - {{ theme('name') }} CMS</title>

    <!-- Favicon (theme-driven) -->
    <link rel="icon" type="image/png" href="{{ asset(theme('logo.favicon')) }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset(theme('logo.favicon')) }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Google Fonts (theme-driven) -->
    <link href="{{ theme('fonts.google_url') }}" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        /* Neutral admin palette */
                        'admin-sidebar': '#1e293b',
                        'admin-content': '#f1f5f9',
                        'admin-header': '#1e293b',
                        'admin-border': 'rgba(255,255,255,0.1)',
                        /* Theme-driven accent */
                        'accent': '{{ theme('colors.accent') }}',
                    },
                    fontFamily: {
                        'heading': {!! json_encode([theme('fonts.heading')]) !!},
                        'sans': {!! json_encode([theme('fonts.body')]) !!},
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-[#f1f5f9] font-sans">
    @php $accentColor = theme('colors.accent'); @endphp
    <div class="flex h-screen">
        <!-- Sidebar (neutral dark admin) -->
        <aside class="w-64 bg-[#1e293b] border-r border-white/10 flex flex-col">
            <div class="p-6 border-b border-white/10">
                <h1 class="text-2xl font-heading tracking-wider text-white">{{ theme('name') }}</h1>
                <p class="text-white/30 text-xs uppercase tracking-widest mt-1">Admin Panel</p>
            </div>
            <nav class="mt-4 flex-1">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition {{ request()->routeIs('admin.dashboard') ? "bg-white/5 border-r-2 border-[{$accentColor}] text-[{$accentColor}]" : 'text-white/60' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition {{ request()->routeIs('admin.categories.*') ? "bg-white/5 border-r-2 border-[{$accentColor}] text-[{$accentColor}]" : 'text-white/60' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Categories
                </a>
                <a href="{{ route('admin.banners.index') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition {{ request()->routeIs('admin.banners.*') ? "bg-white/5 border-r-2 border-[{$accentColor}] text-[{$accentColor}]" : 'text-white/60' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Banners
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition {{ request()->routeIs('admin.products.*') ? "bg-white/5 border-r-2 border-[{$accentColor}] text-[{$accentColor}]" : 'text-white/60' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Products
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition {{ request()->routeIs('admin.orders.*') ? "bg-white/5 border-r-2 border-[{$accentColor}] text-[{$accentColor}]" : 'text-white/60' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Orders
                </a>
                <a href="{{ route('admin.coupons.index') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition {{ request()->routeIs('admin.coupons.*') ? "bg-white/5 border-r-2 border-[{$accentColor}] text-[{$accentColor}]" : 'text-white/60' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    Coupons
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition {{ request()->routeIs('admin.reviews.*') ? "bg-white/5 border-r-2 border-[{$accentColor}] text-[{$accentColor}]" : 'text-white/60' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    Reviews
                </a>
                <a href="{{ route('admin.alerts.index') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition {{ request()->routeIs('admin.alerts.*') ? "bg-white/5 border-r-2 border-[{$accentColor}] text-[{$accentColor}]" : 'text-white/60' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>Inventory Alerts</span>
                    @if(($unresolvedAlertCount ?? 0) > 0)
                        <span class="ml-auto inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-semibold rounded-full bg-red-500 text-white">{{ $unresolvedAlertCount > 99 ? '99+' : $unresolvedAlertCount }}</span>
                    @endif
                </a>
                <hr class="border-white/10 my-4">
                <a href="{{ route('superadmin.sites.index') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition {{ request()->routeIs('superadmin.*') ? "bg-white/5 border-r-2 border-[{$accentColor}] text-[{$accentColor}]" : 'text-white/60' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                    Site Management
                </a>
                <a href="{{ route('home') }}" class="flex items-center py-3 px-6 hover:bg-white/5 transition text-white/40">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    View Site
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center py-3 px-6 hover:bg-white/5 transition text-red-400/70 hover:text-red-400">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <!-- Top Bar (neutral admin) -->
            <header class="bg-[#1e293b] border-b border-white/10">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h2 class="text-xl font-heading tracking-wide text-white">@yield('page-title', 'Dashboard')</h2>
                    <div class="flex items-center space-x-4">
                        <span class="text-white/50 text-sm">{{ auth()->user()->name }}</span>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm" style="background-color: {{ $accentColor }}33; border: 1px solid {{ $accentColor }}4d; color: {{ $accentColor }}">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-900/50 border border-green-500/30 text-green-300 px-4 py-3 rounded" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-900/50 border border-red-500/30 text-red-300 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
