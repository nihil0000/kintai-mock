<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Auth\Events\Registered;

class AuthenticatedSessionController extends Controller
{
    // Display login form
    public function create()
    {
        return view('auth.login');
    }

    // Login
    public function store(LoginRequest $request)
    {
        // Validate email and password
        $credentials = $request->validated();

        // Attempt authentication
        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            /** @var \App\Models\User|\Illuminate\Contracts\Auth\MustVerifyEmail $user */
            if (!$user->hasVerifiedEmail()) {
                event(new Registered($user));

                return redirect()->route('verification.notice');
            }

            $request->session()->regenerate(); // Prevent session fixation attacks
            return redirect()->intended(route('attendance.index')); // Redirect admin page
        }

        // Authentication failed
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。'
        ])->withInput();
    }

    // Logout
    public function destroy(Request $request)
    {
        auth()->logout(); // Logout the user
        $request->session()->invalidate(); // Invalidate the session
        $request->session()->regenerateToken(); // Regenerate CSRF token
        return redirect()->route('login.create'); // Redirect to login page
    }
}
