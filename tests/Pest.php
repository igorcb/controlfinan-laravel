<?php

use App\Models\CurrentAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature', 'Unit');

function createUser(string $email = 'driver@example.com', string $password = 'secret123'): User
{
    $user = new User(['email_address' => $email]);
    $user->setPassword($password);
    $user->save();

    return $user;
}

/** Signs in like the Rails app: a row in `sessions` plus the "session_id" cookie. */
function signIn(?User $user = null): User
{
    $user ??= createUser();
    $session = $user->sessions()->create(['ip_address' => '127.0.0.1', 'user_agent' => 'Pest']);
    test()->withCookie('session_id', (string) $session->id);

    return $user;
}

function createEntry(User $user, array $attributes = []): CurrentAccount
{
    $entry = new CurrentAccount(array_merge([
        'cash_account_id' => 5,
        'cost_id' => 6,
        'type_launche' => 1,
        'date_ocurrence' => '2026-10-27',
        'price' => '150.00',
        'historic' => 'Corridas',
    ], $attributes));
    $entry->user_id = $user->id;
    $entry->save();

    return $entry;
}
