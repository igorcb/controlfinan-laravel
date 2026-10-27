<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;

describe('Passwords', function () {
    it('sends the reset e-mail without revealing whether the address exists', function () {
        createUser('driver@example.com');
        $notice = 'Se o e-mail estiver cadastrado, enviamos as instruções para redefinir a senha.';

        $this->post('/passwords', ['email_address' => 'driver@example.com'])->assertRedirect('/session/new')->assertSessionHas('notice', $notice);
        $this->post('/passwords', ['email_address' => 'missing@example.com'])->assertSessionHas('notice', $notice);

        $sent = app('mailer')->getSymfonyTransport()->messages();
        expect($sent)->toHaveCount(1)
            ->and($sent[0]->getOriginalMessage()->getSubject())->toBe('Redefinição de senha')
            ->and($sent[0]->toString())->toContain('15 minutos');
    });

    it('resets the password and ends every session', function () {
        $user = signIn(createUser('driver@example.com', 'old-secret'));
        $token = $user->passwordResetToken();

        $this->get("/passwords/{$token}/edit")->assertOk()->assertSee('Defina sua nova senha');
        $this->put("/passwords/{$token}", ['password' => 'new-secret', 'password_confirmation' => 'new-secret'])
            ->assertRedirect('/session/new')->assertSessionHas('notice', 'Senha redefinida.');

        expect(User::authenticateBy('driver@example.com', 'new-secret'))->not->toBeNull()
            ->and($user->sessions()->count())->toBe(0);
    });

    it('rejects passwords that do not match', function () {
        $token = createUser()->passwordResetToken();

        $this->put("/passwords/{$token}", ['password' => 'new-secret', 'password_confirmation' => 'other'])
            ->assertRedirect("/passwords/{$token}/edit")
            ->assertSessionHas('alert', 'As senhas não conferem ou têm menos de 6 caracteres.');
    });

    it('rejects an invalid token', function () {
        $this->get('/passwords/invalid/edit')
            ->assertRedirect('/passwords/new')
            ->assertSessionHas('alert', 'O link de redefinição é inválido ou expirou.');
    });
});
