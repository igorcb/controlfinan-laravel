<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PasswordsController extends Controller
{
    public function create(Request $request)
    {
        return view('passwords.new', ['emailAddress' => $request->query('email_address', '')]);
    }

    public function store(Request $request)
    {
        $user = User::where('email_address', User::normalizeEmail($request->input('email_address')))->first();
        if ($user) {
            $data = ['url' => url('/passwords/'.$user->passwordResetToken().'/edit'), 'expiresIn' => '15 minutos'];
            Mail::send(['html' => 'emails.reset', 'text' => 'emails.reset_text'], $data, function ($message) use ($user) {
                $message->to($user->email_address)->subject('Redefinição de senha');
            });
        }

        return redirect('/session/new')->with('notice', 'Se o e-mail estiver cadastrado, enviamos as instruções para redefinir a senha.');
    }

    public function edit(string $token)
    {
        if (! User::findByPasswordResetToken($token)) {
            return redirect('/passwords/new')->with('alert', 'O link de redefinição é inválido ou expirou.');
        }

        return view('passwords.edit', ['token' => $token]);
    }

    public function update(Request $request, string $token)
    {
        $user = User::findByPasswordResetToken($token);
        if (! $user) {
            return redirect('/passwords/new')->with('alert', 'O link de redefinição é inválido ou expirou.');
        }
        $password = (string) $request->input('password', '');
        if ($user->validate($password, (string) $request->input('password_confirmation', ''), true)) {
            return redirect("/passwords/{$token}/edit")->with('alert', 'As senhas não conferem ou têm menos de 6 caracteres.');
        }
        $user->setPassword($password);
        $user->save();
        $user->sessions()->delete();

        return redirect('/session/new')->with('notice', 'Senha redefinida.');
    }
}
