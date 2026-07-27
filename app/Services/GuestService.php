<?php

namespace App\Services;

use App\Models\GuestSession;
use Illuminate\Support\Facades\Cookie;
use Carbon\Carbon;

class GuestService
{
    public function getCurrentGuestSession()
    {
        $token = Cookie::get('guest_session_token');

        if (!$token) {
            $token = $this->createGuestSession();
        }

        $session = GuestSession::where('session_token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$session) {
            $token = $this->createGuestSession();
            $session = GuestSession::where('session_token', $token)->first();
        }

        return $session;
    }

    public function createGuestSession()
    {
        $token = bin2hex(random_bytes(32));

        GuestSession::create([
            'session_token' => $token,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        Cookie::queue('guest_session_token', $token, 43200); // 30 days

        return $token;
    }

    public function transferGuestDataToUser($userId)
    {
        // No guest session support - carts are user-only
        // This method kept for future guest wishlist implementation
        \Log::info('Guest data transfer for user ID: ' . $userId);
    }
}
