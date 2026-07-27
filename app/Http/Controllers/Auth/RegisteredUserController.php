<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GuestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    protected $guestService;

    public function __construct(GuestService $guestService)
    {
        $this->guestService = $guestService;
    }

    /**
     * Display registration form
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullName' => 'required|string|max:150',
            'identifier' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Determine if identifier is email or phone
        $field = filter_var($validated['identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        $userData = [
            'name' => $validated['fullName'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'status' => 'active',
        ];

        $userData[$field] = $validated['identifier'];

        $user = User::create($userData);

        // Login the user
        Auth::login($user);

        // Transfer guest data to newly registered user
        $this->guestService->transferGuestDataToUser($user->id);

        return redirect()->route('home')
            ->with('success', 'Welcome to BAD LAB, ' . $user->name . '!');
    }
}
