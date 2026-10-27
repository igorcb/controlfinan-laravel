<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class User extends Model
{
    // Same regexp as Ruby's URI::MailTo::EMAIL_REGEXP.
    public const EMAIL_REGEXP = '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/';

    public const PASSWORD_RESET_MAX_AGE = 15 * 60; // Rails 8 has_secure_password default

    protected $fillable = ['email_address'];

    protected $hidden = ['password_digest'];

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function currentAccounts(): HasMany
    {
        return $this->hasMany(CurrentAccount::class);
    }

    public static function normalizeEmail(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    // has_secure_password: bcrypt digests in the "$2a$" format Rails writes ($2y$ is the same algorithm).
    public function setPassword(string $raw): void
    {
        $cost = (int) config('hashing.bcrypt.rounds', 12);
        $digest = password_hash(substr($raw, 0, 72), PASSWORD_BCRYPT, ['cost' => $cost]);
        $this->password_digest = '$2a$'.substr($digest, 4);
    }

    public function checkPassword(?string $raw): bool
    {
        return $raw !== null && $raw !== '' && $this->password_digest
            && password_verify(substr($raw, 0, 72), $this->password_digest);
    }

    public static function authenticateBy(?string $email, ?string $password): ?self
    {
        $user = static::where('email_address', static::normalizeEmail($email))->first();
        if (! $user) {
            // Burn the same time as a real check to avoid user enumeration by timing.
            password_verify('x', password_hash('y', PASSWORD_BCRYPT, ['cost' => (int) config('hashing.bcrypt.rounds', 12)]));

            return null;
        }

        return $user->checkPassword($password) ? $user : null;
    }

    /** pt-BR messages in the same order and wording as the Rails app. */
    public function validate(?string $password = null, ?string $confirmation = null, bool $passwordRequired = false): array
    {
        $errors = [];
        if ($passwordRequired && ($password === null || $password === '')) {
            $errors[] = 'Senha não pode ficar em branco';
        }
        if ($password !== null && $confirmation !== null && $password !== $confirmation) {
            $errors[] = 'Confirmação da senha não é igual a Senha';
        }
        $this->email_address = static::normalizeEmail($this->email_address);
        if ($this->email_address === '') {
            $errors[] = 'E-mail não pode ficar em branco';
        } elseif (! preg_match(self::EMAIL_REGEXP, $this->email_address)) {
            $errors[] = 'E-mail não é válido';
        } elseif (static::where('email_address', $this->email_address)->where('id', '!=', $this->id ?? 0)->exists()) {
            $errors[] = 'E-mail já está em uso';
        }
        if ($password !== null && $password !== '' && mb_strlen($password) < 6) {
            $errors[] = 'Senha é muito curto (mínimo: 6 caracteres)';
        }

        return $errors;
    }

    // Reset token: encrypted, expires in 15 minutes and stops working once the password changes.
    public function passwordResetToken(): string
    {
        $payload = json_encode(['id' => $this->id, 'v' => substr($this->password_digest, -10), 'exp' => now()->timestamp + self::PASSWORD_RESET_MAX_AGE]);

        return rtrim(strtr(base64_encode(Crypt::encryptString($payload)), '+/', '-_'), '=');
    }

    public static function findByPasswordResetToken(string $token): ?self
    {
        try {
            $data = json_decode(Crypt::decryptString(base64_decode(strtr($token, '-_', '+/'))), true);
        } catch (\Throwable) {
            return null;
        }
        if (! is_array($data) || ($data['exp'] ?? 0) < now()->timestamp) {
            return null;
        }
        $user = static::find($data['id'] ?? 0);

        return $user && substr($user->password_digest, -10) === ($data['v'] ?? null) ? $user : null;
    }
}
