<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Auth;
use Illuminate\Http\Request;

class SessionsController extends Controller
{
    public function create(Request $request)
    {
        return view('sessions.new', ['emailAddress' => $request->query('email_address', '')]);
    }

    public function store(Request $request)
    {
        $user = User::authenticateBy($request->input('email_address'), $request->input('password'));
        if (! $user) {
            return redirect('/session/new')->with('alert', 'E-mail ou senha inválidos.');
        }
        Auth::startNewSessionFor($request, $user);

        return redirect(Auth::afterAuthenticationUrl($request));
    }

    public function destroy()
    {
        Auth::terminate();

        return redirect('/session/new', 303);
    }
}
