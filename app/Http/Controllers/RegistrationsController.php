<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Auth;
use Illuminate\Http\Request;

class RegistrationsController extends Controller
{
    public function create()
    {
        return view('registrations.new', ['user' => new User(['email_address' => '']), 'errors' => []]);
    }

    public function store(Request $request)
    {
        $data = (array) $request->input('user', []);
        $user = new User(['email_address' => (string) ($data['email_address'] ?? '')]);
        $password = (string) ($data['password'] ?? '');
        $errors = $user->validate($password, (string) ($data['password_confirmation'] ?? ''), true);
        if ($errors) {
            return response()->view('registrations.new', ['user' => $user, 'errors' => $errors], 422);
        }
        $user->setPassword($password);
        $user->save();
        Auth::startNewSessionFor($request, $user);

        return redirect('/')->with('notice', 'Conta criada.');
    }
}
