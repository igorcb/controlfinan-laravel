<?php

use App\Models\User;

describe('Registrations', function () {
    it('creates an account and signs in', function () {
        $this->post('/registration', ['user' => [
            'email_address' => ' New@Example.com ', 'password' => 'secret123', 'password_confirmation' => 'secret123',
        ]])->assertRedirect('/')->assertSessionHas('notice', 'Conta criada.')->assertCookie('session_id');

        expect(User::first()->email_address)->toBe('new@example.com');
    });

    it('shows the validation messages', function () {
        $this->post('/registration', ['user' => ['email_address' => 'bad', 'password' => '1', 'password_confirmation' => '2']])
            ->assertStatus(422)
            ->assertSee('Confirmação da senha não é igual a Senha')
            ->assertSee('E-mail não é válido');
    });
});
