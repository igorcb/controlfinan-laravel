<?php

namespace App\Support;

use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/** Authentication like Rails 8's generator: an encrypted "session_id" cookie pointing to a row in `sessions`. */
class Auth
{
    public const COOKIE = 'session_id';

    private const PERMANENT_MINUTES = 20 * 365 * 24 * 60; // cookies.permanent

    public static ?Session $session = null;

    public static function user(): ?User
    {
        return self::$session?->user;
    }

    public static function resume(Request $request): ?Session
    {
        $id = $request->cookie(self::COOKIE);
        self::$session = ctype_digit((string) $id) ? Session::with('user')->find($id) : null;

        return self::$session;
    }

    public static function startNewSessionFor(Request $request, User $user): void
    {
        self::$session = $user->sessions()->create([
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            'ip_address' => $request->ip(),
        ]);
        self::$session->setRelation('user', $user);
        Cookie::queue(Cookie::make(self::COOKIE, (string) self::$session->id, self::PERMANENT_MINUTES, null, null, null, true, false, 'lax'));
    }

    public static function terminate(): void
    {
        self::$session?->delete();
        self::$session = null;
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    public static function afterAuthenticationUrl(Request $request): string
    {
        return $request->session()->pull('return_to_after_authenticating', url('/'));
    }
}
