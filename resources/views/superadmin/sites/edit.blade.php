@extends('layouts.admin')

@section('title', 'Edit Site: ' . $theme['name'])
@section('page-title', 'Edit Site: ' . $theme['name'])

@section('content')
<div class="mb-6">
    <a href="{{ route('superadmin.sites.index') }}" class="inline-flex items-center text-sm text-white/50 hover:text-white transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to Sites
    </a>
</div>

@if(session('error'))
    <div class="mb-4 bg-red-900/50 border border-red-500/30 text-red-300 px-4 py-3 rounded" role="alert">
        {{ session('error') }}
    </div>
@endif

<form method="POST" action="{{ route('superadmin.sites.update', $slug) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- General Settings -->
        <div class="bg-[#1a1a1a] border border-white/10 rounded-lg">
            <div class="px-6 py-4 border-b border-white/10">
                <h3 class="text-lg font-heading tracking-wide text-white">GENERAL <span class="text-accent">SETTINGS</span></h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-white/50 text-sm uppercase tracking-wider mb-1">Site Name</label>
                    <input type="text" name="name" value="{{ old('name', $overrides['name'] ?? $theme['name'] ?? '') }}"
                        class="w-full bg-black border border-white/20 text-white px-4 py-2 rounded focus:border-accent focus:outline-none transition" />
                </div>
                <div>
                    <label class="block text-white/50 text-sm uppercase tracking-wider mb-1">Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $overrides['tagline'] ?? $theme['tagline'] ?? '') }}"
                        class="w-full bg-black border border-white/20 text-white px-4 py-2 rounded focus:border-accent focus:outline-none transition" />
                </div>
                <div>
                    <label class="block text-white/50 text-sm uppercase tracking-wider mb-1">Slug</label>
                    <input type="text" value="{{ $slug }}" disabled
                        class="w-full bg-black/50 border border-white/10 text-white/30 px-4 py-2 rounded cursor-not-allowed" />
                </div>
            </div>
        </div>

        <!-- Color Settings -->
        <div class="bg-[#1a1a1a] border border-white/10 rounded-lg">
            <div class="px-6 py-4 border-b border-white/10">
                <h3 class="text-lg font-heading tracking-wide text-white">COLOR <span class="text-accent">PALETTE</span></h3>
            </div>
            <div class="p-6 space-y-4">
                @php
                    $colorFields = [
                        'primary' => 'Primary',
                        'accent' => 'Accent',
                        'secondary' => 'Secondary',
                        'bg_primary' => 'Background Primary',
                        'bg_secondary' => 'Background Secondary',
                    ];
                    $themeColors = $theme['colors'] ?? [];
                @endphp

                @foreach($colorFields as $key => $label)
                    @php
                        $currentValue = old("colors.{$key}", $overrides["colors.{$key}"] ?? ($themeColors[$key] ?? '#000000'));
                    @endphp
                    <div class="flex items-center gap-3">
                        <label class="text-white/50 text-sm uppercase tracking-wider w-40 shrink-0">{{ $label }}</label>
                        <input type="color" name="colors[{{ $key }}]" value="{{ $currentValue }}"
                            class="w-10 h-10 rounded border border-white/20 cursor-pointer bg-transparent" />
                        <input type="text" value="{{ $currentValue }}" readonly
                            class="flex-1 bg-black border border-white/20 text-white/60 px-3 py-2 rounded text-sm font-mono" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="mt-6 flex items-center gap-4">
        <button type="submit" class="px-6 py-3 bg-accent text-black font-medium rounded hover:opacity-90 transition">
            Save Changes
        </button>
        <a href="{{ route('superadmin.sites.index') }}" class="px-6 py-3 border border-white/10 text-white/60 rounded hover:bg-white/5 transition">
            Cancel
        </a>
    </div>
</form>

<!-- Site Files Info (read-only) -->
<div class="mt-8 bg-[#1a1a1a] border border-white/10 rounded-lg">
    <div class="px-6 py-4 border-b border-white/10">
        <h3 class="text-lg font-heading tracking-wide text-white">SITE <span class="text-accent">FILES</span></h3>
    </div>
    <div class="p-6">
        <div class="text-sm text-white/50 space-y-2">
            <div>📁 Config: <code class="px-1 py-0.5 rounded bg-white/5">sites/{{ $slug }}/site.php</code></div>
            <div>🎨 CSS: <code class="px-1 py-0.5 rounded bg-white/5">sites/{{ $slug }}/css/site.css</code></div>
            <div>👁️ Views: <code class="px-1 py-0.5 rounded bg-white/5">sites/{{ $slug }}/views/partials/</code></div>
            <div>🗄️ Database: <code class="px-1 py-0.5 rounded bg-white/5">{{ $theme['database'] ?? "sites/{$slug}/database/site.sqlite" }}</code></div>
            <div>🌱 Seeders: <code class="px-1 py-0.5 rounded bg-white/5">sites/{{ $slug }}/seeders/</code></div>
            <div>⚙️ Env: <code class="px-1 py-0.5 rounded bg-white/5">sites/{{ $slug }}/.env</code></div>
        </div>
    </div>
</div>

<!-- Site Info (read-only) -->
<div class="mt-6 bg-[#1a1a1a] border border-white/10 rounded-lg">
    <div class="px-6 py-4 border-b border-white/10">
        <h3 class="text-lg font-heading tracking-wide text-white">SITE <span class="text-accent">INFO</span></h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-white/30 uppercase tracking-wider text-xs mb-1">Fonts</p>
                <p class="text-white/70">{{ $theme['fonts']['heading_name'] ?? 'N/A' }} (heading)</p>
                <p class="text-white/70">{{ $theme['fonts']['body_name'] ?? 'N/A' }} (body)</p>
            </div>
            <div>
                <p class="text-white/30 uppercase tracking-wider text-xs mb-1">Currency</p>
                <p class="text-white/70">{{ $theme['currency']['symbol'] ?? '' }} {{ $theme['currency']['code'] ?? '' }}</p>
            </div>
            <div>
                <p class="text-white/30 uppercase tracking-wider text-xs mb-1">Homepage Sections</p>
                <p class="text-white/70">{{ count($theme['homepage_sections'] ?? []) }} sections</p>
            </div>
            <div>
                <p class="text-white/30 uppercase tracking-wider text-xs mb-1">Contact</p>
                <p class="text-white/70">{{ $theme['contact']['email'] ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
