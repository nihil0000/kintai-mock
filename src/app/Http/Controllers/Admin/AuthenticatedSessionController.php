<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    /**
     * ログイン処理
     */
    public function store(LoginRequest $request)
    {
        // Validate email and password
        $credentials = $request->validated();

        if (auth()->guard('admins')->attempt($credentials)) {
            $request->session()->regenerate(); // Prevent session fixation attacks
            return redirect()->intended(route('admin.attendance.index')); // Redirect attendance list page
        }

        // Authentication failed
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません。'
        ])->withInput();
    }

    // Logout
    public function destroy(Request $request)
    {
        auth()->logout(); // Logout the admin user
        $request->session()->invalidate(); // Invalidate the session
        $request->session()->regenerateToken(); // Regenerate CSRF token

        return redirect()->route('admin.login.create');
    }
}
