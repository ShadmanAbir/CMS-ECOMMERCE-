@extends('layouts.app')

@section('title', theme('name') . ' - ' . theme('tagline'))

@section('content')
{{-- Page Loader (theme-specific) --}}
@if(theme('features.page_loader'))
<div class="page-loader">
    <div class="loader-text">{{ theme('name') }}</div>
</div>
@endif

<div class="min-h-screen">
    {{-- Render homepage sections in configured order --}}
    @foreach(theme('homepage_sections') as $section)
        @if(View::exists("theme::partials._{$section}"))
            @include("theme::partials._{$section}")
        @endif
    @endforeach

    {{-- Section Divider --}}
    <div class="w-full h-px bg-accent/30"></div>
</div>
@endsection
