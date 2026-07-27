<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GuestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    protected $guestService;

    public function __construct(GuestService $guestService)
    {
        $this->guestService = $guestService;
    }

    /**
     * Redirect to provider (Google, Facebook, Apple)
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback from provider
     */
    public function callback(string $provider)
    {
        if (!in_array($provider, ['google', 'facebook', 'apple'])) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Failed to authenticate with ' . ucfirst($provider) . '. Please try again.');
        }

        // Find or create user
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (!$user) {
            // Check if email already exists
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Link social account to existing user
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar() ?? $user->avatar,
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                    'role' => 'customer',
                    'status' => 'active',
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);
            }
        }

        // Login the user
        Auth::login($user, true);

        // Transfer guest data
        $this->guestService->transferGuestDataToUser($user->id);

        // Redirect based on role
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . $user->name);
        }

        return redirect()->intended(route('home'))
            ->with('success', 'Welcome, ' . $user->name);
    }
}
