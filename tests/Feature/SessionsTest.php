<?php

use App\Models\Session;

describe('Sessions', function () {
    it('shows the login form', function () {
        $this->get('/session/new')->assertOk()->assertSee('Esqueceu a senha?');
    });

    it('redirects guests to the login and back after signing in', function () {
        createUser('driver@example.com', 'secret123');

        $this->get('/dashboard')->assertRedirect('/session/new');
        $this->post('/session', ['email_address' => 'driver@example.com', 'password' => 'secret123'])
            ->assertRedirect('http://localhost/dashboard')
            ->assertCookie('session_id');

        expect(Session::count())->toBe(1);
    });

    it('rejects a wrong password', function () {
        createUser('driver@example.com', 'secret123');

        $this->post('/session', ['email_address' => 'driver@example.com', 'password' => 'wrong'])
            ->assertRedirect('/session/new')
            ->assertSessionHas('alert', 'E-mail ou senha inválidos.');
    });

    it('limits login attempts to 10 every 3 minutes', function () {
        foreach (range(1, 10) as $_) {
            $this->post('/session', ['email_address' => 'x@example.com', 'password' => 'x']);
        }

        $this->post('/session', ['email_address' => 'x@example.com', 'password' => 'x'])
            ->assertSessionHas('alert', 'Tente novamente mais tarde.');
    });

    it('signs out and deletes the session row', function () {
        signIn();

        $this->delete('/session')->assertStatus(303)->assertRedirect('/session/new');
        expect(Session::count())->toBe(0);
    });
});
