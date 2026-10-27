<?php

describe('Dashboards', function () {
    it('shows the daily and monthly balances', function () {
        $user = signIn();
        createEntry($user, ['date_ocurrence' => '2026-10-02', 'cost_id' => 6, 'price' => '200.00']);
        createEntry($user, ['date_ocurrence' => '2026-10-02', 'cost_id' => 0, 'type_launche' => -1, 'price' => '50.00']);
        createEntry($user, ['date_ocurrence' => '2026-10-05', 'cost_id' => 8, 'type_launche' => -1, 'price' => '20.00']);
        createEntry($user, ['date_ocurrence' => '2026-09-30', 'price' => '999.00']);

        $this->get('/dashboard?month=2026-10')->assertOk()
            ->assertSee('Outubro de 2026')
            ->assertSeeInOrder(['Saldo Diário', 'Saldo Mensal', '05/10/2026', '-R$ 20,00', '02/10/2026', 'R$ 150,00', 'Total do mês', 'R$ 130,00'])
            ->assertSeeInOrder(['Corridas', 'R$ 200,00', 'Combustível', '-R$ 50,00', 'Lavagem', '-R$ 20,00', 'Telefone', 'R$ 0,00', 'Saldo do mês', 'R$ 130,00'])
            ->assertDontSee('999');
    });

    it('links to the previous and next months', function () {
        signIn();

        $this->get('/dashboard?month=2026-01')->assertSee('/dashboard?month=2025-12')->assertSee('/dashboard?month=2026-02');
    });

    it('falls back to the current month for an invalid month', function () {
        signIn();
        $this->travelTo('2026-03-15');

        $this->get('/dashboard?month=bad')->assertSee('Março de 2026');
    });
});
