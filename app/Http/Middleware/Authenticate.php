<?php

namespace App\Http\Middleware;

use App\Support\Auth;
use Closure;
use Illuminate\Http\Request;

/** Loads the current session; with the "required" argument redirects to the login when there is none. */
class Authenticate
{
    public function handle(Request $request, Closure $next, ?string $mode = null)
    {
        Auth::resume($request);
        view()->share('currentUser', Auth::user());

        if ($mode === 'required' && ! Auth::user()) {
            $request->session()->put('return_to_after_authenticating', $request->fullUrl());

            return redirect('/session/new');
        }

        return $next($request);
    }
}
