<?php

use App\Models\User;

describe('User', function () {
    it('stores bcrypt digests in the $2a$ format Rails uses', function () {
        expect(createUser()->password_digest)->toStartWith('$2a$');
    });

    it('accepts a password digest written by Rails', function () {
        $user = new User(['email_address' => 'rails@example.com']);
        $user->password_digest = '$2a$04$'.substr(password_hash('benchmark123', PASSWORD_BCRYPT, ['cost' => 4]), 7);

        expect($user->checkPassword('benchmark123'))->toBeTrue()
            ->and($user->checkPassword('wrong'))->toBeFalse();
    });

    it('authenticates by normalized email and password', function () {
        $user = createUser('driver@example.com', 'secret123');

        expect(User::authenticateBy('  Driver@Example.COM ', 'secret123')?->id)->toBe($user->id)
            ->and(User::authenticateBy('driver@example.com', 'nope'))->toBeNull()
            ->and(User::authenticateBy('missing@example.com', 'secret123'))->toBeNull();
    });

    it('returns the Rails validation messages in pt-BR', function () {
        createUser('taken@example.com');

        expect((new User(['email_address' => '']))->validate('', '', true))
            ->toBe(['Senha não pode ficar em branco', 'E-mail não pode ficar em branco'])
            ->and((new User(['email_address' => 'bad']))->validate('123', '456', true))
            ->toBe(['Confirmação da senha não é igual a Senha', 'E-mail não é válido', 'Senha é muito curto (mínimo: 6 caracteres)'])
            ->and((new User(['email_address' => 'TAKEN@example.com']))->validate('secret123', 'secret123', true))
            ->toBe(['E-mail já está em uso']);
    });

    it('finds the user by a valid password reset token', function () {
        $user = createUser();

        expect(User::findByPasswordResetToken($user->passwordResetToken())?->id)->toBe($user->id);
    });

    it('rejects a reset token after the password changes', function () {
        $user = createUser();
        $token = $user->passwordResetToken();
        $user->setPassword('another123');
        $user->save();

        expect(User::findByPasswordResetToken($token))->toBeNull();
    });

    it('rejects a reset token after 15 minutes', function () {
        $user = createUser();
        $token = $user->passwordResetToken();
        $this->travel(16)->minutes();

        expect(User::findByPasswordResetToken($token))->toBeNull();
    });

    it('rejects a tampered reset token', function () {
        expect(User::findByPasswordResetToken('garbage'))->toBeNull();
    });
});
