<?php

use App\Models\CurrentAccount;
use Carbon\CarbonImmutable;

describe('CurrentAccount', function () {
    it('computes the balance as credits minus debits', function () {
        $user = createUser();
        createEntry($user, ['type_launche' => 1, 'price' => '150.00']);
        createEntry($user, ['type_launche' => -1, 'price' => '40.50', 'cost_id' => 0]);

        expect((float) CurrentAccount::balanceOf(CurrentAccount::where('user_id', $user->id)))->toBe(109.5);
    });

    it('groups the balance by day, most recent first', function () {
        $user = createUser();
        createEntry($user, ['date_ocurrence' => '2026-10-01', 'price' => '10.00']);
        createEntry($user, ['date_ocurrence' => '2026-10-02', 'price' => '20.00']);
        createEntry($user, ['date_ocurrence' => '2026-10-02', 'price' => '5.00', 'type_launche' => -1]);

        $rows = CurrentAccount::balanceByDay(CurrentAccount::where('user_id', $user->id));

        expect(array_keys($rows))->toBe(['2026-10-02', '2026-10-01'])
            ->and(array_map('floatval', array_values($rows)))->toBe([15.0, 10.0]);
    });

    it('filters entries by month', function () {
        $user = createUser();
        createEntry($user, ['date_ocurrence' => '2026-09-30']);
        createEntry($user, ['date_ocurrence' => '2026-10-31']);

        expect(CurrentAccount::where('user_id', $user->id)->inMonth(CarbonImmutable::create(2026, 10, 1))->count())->toBe(1);
    });

    it('names accounts and categories with a fallback', function () {
        $entry = new CurrentAccount(['cash_account_id' => 1, 'cost_id' => 99, 'type_launche' => -1, 'price' => '10.00']);

        expect($entry->nameAccount())->toBe('Itaú')
            ->and($entry->nameCost())->toBe('Não definido')
            ->and($entry->typeLauncheName())->toBe('Débito')
            ->and($entry->signedPrice())->toBe('-10.00');
    });

    it('returns the Rails validation messages in pt-BR', function () {
        $entry = new CurrentAccount;

        expect($entry->validate())->toBe([
            'Tipo não está incluído na lista',
            'Conta não está incluído na lista',
            'Categoria não está incluído na lista',
            'Data não pode ficar em branco',
            'Valor não pode ficar em branco',
            'Valor não é um número',
        ]);
    });

    it('requires a positive price', function () {
        $entry = new CurrentAccount(['cash_account_id' => 0, 'cost_id' => 0, 'type_launche' => 1, 'date_ocurrence' => '2026-10-27', 'price' => '0']);

        expect($entry->validate())->toBe(['Valor deve ser maior que 0']);
    });
});
