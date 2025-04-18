<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // admins ガードでログインしていなければ管理者ログイン画面へリダイレクト
        if (! auth()->guard('admins')->check()) {
            return redirect()->route('admin.login.create');
        }

        return $next($request);
    }
}
