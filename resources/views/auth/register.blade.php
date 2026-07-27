@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
@php
    $aOverlay   = theme('auth.overlay_class');
    $aCardBg   = theme('auth.card_bg');
    $aCardText = theme('auth.card_text');
    $aCardBorder = theme('auth.card_border');
    $aLabel    = theme('auth.label_text');
    $aInputBg  = theme('auth.input_bg');
    $aInputBorder = theme('auth.input_border');
    $aInputText = theme('auth.input_text');
    $aInputPh  = theme('auth.input_placeholder');
    $aMuted    = theme('auth.muted_text');
    $aMuted2   = theme('auth.muted_text_2');
    $aDivBorder = theme('auth.divider_border');
    $aDivText  = theme('auth.divider_text');
    $aSocialBg = theme('auth.social_bg');
    $aSocialBorder = theme('auth.social_border');
    $aSocialHoverBorder = theme('auth.social_hover_border');
    $aSocialHoverBg = theme('auth.social_hover_bg');
    $aSocialText = theme('auth.social_text');
    $aCheckBorder = theme('auth.checkbox_border');
@endphp
<div class="min-h-screen flex items-center justify-center py-12 px-4 relative">
    <!-- Background Parallax -->
    @if(theme('images_path'))
    <div class="fixed inset-0 z-0" style="background-image: url('{{ asset(theme('images_path') . '/banner2.jpg') }}'); background-size: cover; background-position: center;"></div>
    <div class="fixed inset-0 z-0 {{ $aOverlay }}"></div>
    @else
    <div class="fixed inset-0 z-0 bg-bg-primary"></div>
    @endif

    <div class="max-w-md w-full relative z-10">
        <!-- Logo -->
        <div class="text-center mb-8 reveal">
            <span class="font-heading text-4xl text-white tracking-wider">{{ theme('name') }}</span>
            <h2 class="mt-4 text-3xl font-heading tracking-wide text-white">Create Account</h2>
            <p class="text-white/50 mt-2 text-sm uppercase tracking-widest">{{ theme('tagline') }}</p>
        </div>

        <!-- Registration Form -->
        <div class="{{ $aCardBg }} border {{ $aCardBorder }} rounded-lg p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- Full Name -->
                <div>
                    <label for="fullName" class="block text-sm font-medium {{ $aLabel }} mb-2 uppercase tracking-wider">Full Name</label>
                    <input type="text" id="fullName" name="fullName" value="{{ old('fullName') }}" required autofocus
                           class="w-full px-4 py-3 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} {{ $aInputPh }} focus:outline-none focus:border-accent transition"
                           placeholder="Enter your full name">
                    @error('fullName')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email/Phone -->
                <div>
                    <label for="identifier" class="block text-sm font-medium {{ $aLabel }} mb-2 uppercase tracking-wider">Email or Phone</label>
                    <input type="text" id="identifier" name="identifier" value="{{ old('identifier') }}" required
                           class="w-full px-4 py-3 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} {{ $aInputPh }} focus:outline-none focus:border-accent transition"
                           placeholder="Enter your email or phone number">
                    @error('identifier')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium {{ $aLabel }} mb-2 uppercase tracking-wider">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} {{ $aInputPh }} focus:outline-none focus:border-accent transition"
                           placeholder="Create a password (min 6 characters)">
                    @error('password')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium {{ $aLabel }} mb-2 uppercase tracking-wider">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="w-full px-4 py-3 {{ $aInputBg }} border {{ $aInputBorder }} rounded-lg {{ $aInputText }} {{ $aInputPh }} focus:outline-none focus:border-accent transition"
                           placeholder="Confirm your password">
                </div>

                <!-- Terms -->
                <div class="flex items-start">
                    <input type="checkbox" id="terms" required class="h-4 w-4 text-accent focus:ring-accent {{ $aCheckBorder }} rounded mt-1 {{ $aInputBg }}">
                    <label for="terms" class="ml-2 block text-sm {{ $aMuted }}">
                        I agree to the <a href="#" class="text-accent hover:underline">Terms of Service</a> and <a href="#" class="text-accent hover:underline">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-accent text-dark-color py-3 rounded-lg hover:bg-accent/90 transition font-bold text-lg uppercase tracking-widest">
                    Create Account
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t {{ $aDivBorder }}"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 {{ $aCardBg }} {{ $aDivText }}">Or continue with</span>
                </div>
            </div>

            <!-- Social Login Buttons -->
            <div class="space-y-3">
                <!-- Google -->
                <a href="{{ route('social.redirect', 'google') }}" class="w-full flex items-center justify-center gap-3 px-4 py-3 {{ $aSocialBg }} border {{ $aSocialBorder }} {{ $aSocialHoverBorder }} {{ $aSocialHoverBg }} {{ $aSocialText }} font-medium transition rounded-lg">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continue with Google
                </a>

                <!-- Facebook -->
                <a href="{{ route('social.redirect', 'facebook') }}" class="w-full flex items-center justify-center gap-3 px-4 py-3 {{ $aSocialBg }} border {{ $aSocialBorder }} {{ $aSocialHoverBorder }} {{ $aSocialHoverBg }} {{ $aSocialText }} font-medium transition rounded-lg">
                    <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Continue with Facebook
                </a>

                <!-- Apple -->
                <a href="{{ route('social.redirect', 'apple') }}" class="w-full flex items-center justify-center gap-3 px-4 py-3 {{ $aSocialBg }} border {{ $aSocialBorder }} {{ $aSocialHoverBorder }} {{ $aSocialHoverBg }} {{ $aSocialText }} font-medium transition rounded-lg">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                    </svg>
                    Continue with Apple
                </a>
            </div>

            <!-- Login Link -->
            <p class="text-center text-sm {{ $aMuted }} mt-6">
                Already have an account?
                <a href="{{ route('login') }}" class="text-accent hover:underline font-medium">Login here</a>
            </p>
        </div>
    </div>
</div>
@endsection
