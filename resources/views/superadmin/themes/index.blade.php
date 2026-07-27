@extends('layouts.admin')

@section('title', 'Theme Management')
@section('page-title', 'Theme Management')

@section('content')
<div class="mb-6">
    <p class="text-gray-500 text-sm">Select and activate a theme for the entire site. Changes take effect immediately.</p>
</div>

@if(session('error'))
    <div class="mb-4 bg-red-900/50 border border-red-500/30 text-red-300 px-4 py-3 rounded" role="alert">
        {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @foreach($themes as $slug => $theme)
        @php
            $isActive = $slug === $activeTheme;
            $colors = $theme['colors'] ?? [];
        @endphp
        <div class="bg-[#1a1a1a] border {{ $isActive ? 'border-accent/50' : 'border-white/10' }} rounded-lg overflow-hidden">
            <!-- Color preview bar -->
            <div class="flex h-3">
                @foreach(['primary','secondary','accent','bg_primary','bg_secondary'] as $colorKey)
                    @if(isset($colors[$colorKey]))
                        <div class="flex-1" style="background-color: {{ $colors[$colorKey] }}"></div>
                    @endif
                @endforeach
            </div>

            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-heading tracking-wide text-white">{{ $theme['name'] }}</h3>
                        <p class="text-white/40 text-xs uppercase tracking-widest mt-1">{{ $slug }}</p>
                    </div>
                    @if($isActive)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-300 border border-green-500/30">
                            Active
                        </span>
                    @endif
                </div>

                <p class="text-white/50 text-sm mb-4">{{ $theme['tagline'] ?? '' }}</p>

                <!-- Color swatches -->
                <div class="flex items-center gap-2 mb-5">
                    @foreach(['primary','accent','bg_primary'] as $colorKey)
                        @if(isset($colors[$colorKey]))
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded border border-white/20" style="background-color: {{ $colors[$colorKey] }}"></div>
                                <span class="text-white/30 text-xs">{{ ucfirst(str_replace('_', ' ', $colorKey)) }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Features summary -->
                @if(isset($theme['features']))
                    <div class="mb-5">
                        <p class="text-white/30 text-xs uppercase tracking-wider mb-2">Features</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($theme['features'] as $feature => $enabled)
                                @if($enabled)
                                    <span class="px-2 py-0.5 text-xs rounded bg-white/5 text-white/50 border border-white/10">
                                        {{ ucfirst(str_replace('_', ' ', $feature)) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-4 border-t border-white/10">
                    @if(!$isActive)
                        <form method="POST" action="{{ route('superadmin.themes.activate', $slug) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded text-sm font-medium text-white transition" style="background-color: {{ $colors['accent'] ?? '#6366f1' }}; opacity: 0.85" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.85'">
                                Activate
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('superadmin.themes.edit', $slug) }}" class="px-4 py-2 rounded text-sm font-medium text-white/60 border border-white/10 hover:bg-white/5 hover:text-white transition">
                        Edit
                    </a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
