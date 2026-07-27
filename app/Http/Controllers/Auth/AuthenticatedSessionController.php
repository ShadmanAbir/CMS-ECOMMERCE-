<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GuestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    protected $guestService;

    public function __construct(GuestService $guestService)
    {
        $this->guestService = $guestService;
    }

    /**
     * Display login form
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        // Try to login with email or phone
        $field = filter_var($validated['identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        if (Auth::attempt([$field => $validated['identifier'], 'password' => $validated['password']])) {
            $request->session()->regenerate();

            // Transfer guest data to logged-in user
            $this->guestService->transferGuestDataToUser(auth()->id());

            // Redirect admin users to admin dashboard
            if (auth()->user()->isAdmin()) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome back, ' . auth()->user()->name);
            }

            return redirect()->intended(route('home'))
                ->with('success', 'Welcome back, ' . auth()->user()->name);
        }

        return back()->withErrors([
            'identifier' => 'The provided credentials do not match our records.',
        ])->onlyInput('identifier');
    }

    /**
     * Destroy an authenticated session (logout)
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been logged out');
    }
}
