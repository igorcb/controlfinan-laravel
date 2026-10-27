<?php

use App\Models\CurrentAccount;

describe('CurrentAccounts', function () {
    $valid = ['current_account' => [
        'type_launche' => 'credito', 'price' => '150', 'date_ocurrence' => '2026-10-27',
        'cost_id' => '6', 'cash_account_id' => '5', 'historic' => 'Corridas do dia',
    ]];

    it('lists the entries with the total balance', function () {
        $user = signIn();
        createEntry($user, ['price' => '150.00']);
        createEntry($user, ['price' => '40.00', 'type_launche' => -1, 'cost_id' => 0]);

        $this->get('/current_accounts')->assertOk()
            ->assertSee('Saldo total')->assertSee('R$ 110,00')->assertSee('-R$ 40,00');
    });

    it('filters by day', function () {
        $user = signIn();
        createEntry($user, ['date_ocurrence' => '2026-10-26', 'price' => '10.00']);
        createEntry($user, ['date_ocurrence' => '2026-10-27', 'price' => '20.00']);

        $this->get('/current_accounts?date=2026-10-26')->assertOk()
            ->assertSee('Saldo do dia 26/10/2026')->assertSee('R$ 10,00')->assertDontSee('R$ 20,00');
    });

    it('paginates 30 entries per page', function () {
        $user = signIn();
        foreach (range(1, 31) as $day) {
            createEntry($user, ['date_ocurrence' => '2026-10-01', 'historic' => "entry {$day}"]);
        }

        $this->get('/current_accounts')->assertSee('Mais antigos →')->assertDontSee('← Mais recentes');
        $this->get('/current_accounts?page=2')->assertSee('← Mais recentes')->assertSee('entry 1')->assertDontSee('Mais antigos →');
    });

    it('shows only the signed in user entries', function () {
        signIn();
        $other = createEntry(createUser('other@example.com'));

        $this->get("/current_accounts/{$other->id}")->assertNotFound();
        $this->delete("/current_accounts/{$other->id}")->assertNotFound();
    });

    it('creates an entry', function () use ($valid) {
        $user = signIn();

        $this->post('/current_accounts', $valid)->assertRedirect('/current_accounts')->assertSessionHas('notice', 'Lançamento cadastrado.');

        $entry = CurrentAccount::first();
        expect($entry->user_id)->toBe($user->id)
            ->and($entry->type_launche)->toBe(1)
            ->and((string) $entry->price)->toBe('150.00');
    });

    it('ignores a user_id sent in the form', function () use ($valid) {
        $user = signIn();
        $other = createUser('other@example.com');
        $valid['current_account']['user_id'] = $other->id;

        $this->post('/current_accounts', $valid);

        expect(CurrentAccount::first()->user_id)->toBe($user->id);
    });

    it('shows the validation messages', function () {
        signIn();

        $this->post('/current_accounts', ['current_account' => ['price' => '']])->assertStatus(422)
            ->assertSee('Valor não pode ficar em branco')->assertSee('Tipo não está incluído na lista');
    });

    it('updates an entry', function () use ($valid) {
        $entry = createEntry(signIn());
        $valid['current_account']['price'] = '12.5';

        $this->patch("/current_accounts/{$entry->id}", $valid)
            ->assertRedirect("/current_accounts/{$entry->id}")->assertSessionHas('notice', 'Lançamento atualizado.');

        expect((string) $entry->fresh()->price)->toBe('12.50');
    });

    it('deletes an entry', function () {
        $entry = createEntry(signIn());

        $this->delete("/current_accounts/{$entry->id}")->assertStatus(303)->assertSessionHas('notice', 'Lançamento removido.');
        expect(CurrentAccount::count())->toBe(0);
    });

    it('renders the JSON like jbuilder', function () {
        $entry = createEntry($user = signIn(), ['historic' => 'Café/Almoço']);

        $expected = '[{"id":'.$entry->id.',"cash_account_id":5,"cost_id":6,"date_ocurrence":"2026-10-27","price":"150.0",'
            .'"historic":"Café/Almoço","user_id":'.$user->id.',"type_launche":1,"url":"http://localhost/current_accounts/'.$entry->id.'.json"}]';

        expect($this->get('/current_accounts.json')->assertOk()->getContent())->toBe($expected);
    });

    it('redirects the legacy daily search', function () {
        $this->get('/search_account?date_calendar_now=2026-10-27')->assertRedirect('/current_accounts?date=2026-10-27');
    });
});
