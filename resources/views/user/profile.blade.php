@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
@php
    $pgText = theme('ui.page_text');
    $cardText = theme('ui.card_text');
    $cardBg = theme('ui.card_bg');
    $cardBorder = theme('ui.card_border');
    $muted = theme('ui.muted_text');
    $muted2 = theme('ui.muted_text_2');
    $muted3 = theme('ui.muted_text_3');
    $inputBg = theme('ui.input_bg');
    $inputBorder = theme('ui.input_border');
    $inputText = theme('ui.input_text');
    $inputPh = theme('ui.input_placeholder');
    $divider = theme('ui.divider');
@endphp
<div class="min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-heading tracking-wider {{ $pgText }} mb-8">MY <span class="text-accent">PROFILE</span></h1>

        @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 mb-6 text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Form -->
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('user.profile.update') }}" class="{{ $cardBg }} border {{ $cardBorder }} p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm {{ $muted }} mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" required>
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm {{ $muted }} mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" placeholder="your@email.com">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm {{ $muted }} mb-2">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent" placeholder="01XXXXXXXXX">
                        @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="border-t {{ $divider }} pt-6">
                        <h3 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Change Password</h3>
                        <p class="text-xs {{ $muted3 }} mb-4">Leave blank to keep your current password</p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm {{ $muted }} mb-2">Current Password</label>
                                <input type="password" name="current_password" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent">
                                @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm {{ $muted }} mb-2">New Password</label>
                                    <input type="password" name="password" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent">
                                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-sm {{ $muted }} mb-2">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="w-full px-4 py-3 {{ $inputBg }} border {{ $inputBorder }} {{ $inputText }} {{ $inputPh }} focus:outline-none focus:border-accent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="bg-accent text-black px-8 py-3 hover:bg-accent/90 transition font-bold text-sm uppercase tracking-widest">
                        Save Changes
                    </button>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Account Info -->
                <div class="{{ $cardBg }} border {{ $cardBorder }} p-6">
                    <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Account</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="{{ $muted }}">Role</span>
                            <span class="{{ $cardText }} uppercase">{{ $user->role }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="{{ $muted }}">Status</span>
                            <span class="{{ $user->status === 'active' ? 'text-green-500' : 'text-red-500' }}">{{ $user->status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="{{ $muted }}">Joined</span>
                            <span class="{{ $cardText }}">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Saved Addresses -->
                <div class="{{ $cardBg }} border {{ $cardBorder }} p-6">
                    <h2 class="text-sm font-bold {{ $cardText }} uppercase tracking-widest mb-4">Saved Addresses</h2>
                    @if($addresses->count() > 0)
                    <div class="space-y-3">
                        @foreach($addresses as $address)
                        <div class="text-sm {{ $address->is_default ? 'border border-accent/30 p-3' : 'border ' . $cardBorder . ' p-3' }}">
                            @if($address->is_default)
                            <span class="text-accent text-xs uppercase tracking-widest font-bold">Default</span>
                            @endif
                            <p class="{{ $muted }} mt-1">{{ $address->full_address }}</p>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="{{ $muted3 }} text-sm">No saved addresses</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
