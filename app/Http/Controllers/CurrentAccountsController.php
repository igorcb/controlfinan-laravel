<?php

namespace App\Http\Controllers;

use App\Models\CurrentAccount;
use App\Support\Auth;
use App\Support\Format;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CurrentAccountsController extends Controller
{
    private const PER_PAGE = 30;

    // Compact JSON, like jbuilder.
    private const JSON_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function index(Request $request, ?string $format = null)
    {
        $date = self::parseDate($request->query('date'));
        $scope = $this->currentAccounts();
        if ($date) {
            $scope->onDate($date);
        }
        $page = max((int) $request->query('page', 1), 1);
        $rows = (clone $scope)->ordered()->limit(self::PER_PAGE + 1)->offset(($page - 1) * self::PER_PAGE)->get();
        $entries = $rows->take(self::PER_PAGE);

        if ($format === 'json') {
            return response()->json($entries->map(fn ($entry) => $this->asJson($entry))->values(), 200, [], self::JSON_FLAGS);
        }

        return view('current_accounts.index', [
            'nav' => 'index',
            'date' => $date,
            'balance' => CurrentAccount::balanceOf($scope),
            'page' => $page,
            'nextPage' => $rows->count() > self::PER_PAGE,
            'currentAccounts' => $entries,
        ]);
    }

    public function show(string $id, ?string $format = null)
    {
        $entry = $this->find($id);
        if ($format === 'json') {
            return response()->json($this->asJson($entry), 200, [], self::JSON_FLAGS);
        }

        return view('current_accounts.show', ['currentAccount' => $entry]);
    }

    public function create()
    {
        $entry = new CurrentAccount(['date_ocurrence' => CarbonImmutable::today(), 'type_launche' => -1]);

        return view('current_accounts.new', ['currentAccount' => $entry, 'errors' => []]);
    }

    public function edit(string $id)
    {
        return view('current_accounts.edit', ['currentAccount' => $this->find($id), 'errors' => []]);
    }

    public function store(Request $request)
    {
        $entry = new CurrentAccount;
        $entry->user_id = Auth::user()->id;
        if ($errors = $this->assign($entry, $request)) {
            return response()->view('current_accounts.new', ['currentAccount' => $entry, 'errors' => $errors], 422);
        }
        $entry->save();

        return redirect('/current_accounts')->with('notice', 'Lançamento cadastrado.');
    }

    public function update(Request $request, string $id)
    {
        $entry = $this->find($id);
        if ($errors = $this->assign($entry, $request)) {
            return response()->view('current_accounts.edit', ['currentAccount' => $entry, 'errors' => $errors], 422);
        }
        $entry->save();

        return redirect("/current_accounts/{$entry->id}")->with('notice', 'Lançamento atualizado.');
    }

    public function destroy(string $id)
    {
        $this->find($id)->delete();

        return redirect('/current_accounts', 303)->with('notice', 'Lançamento removido.');
    }

    private function currentAccounts(): Builder
    {
        return CurrentAccount::where('user_id', Auth::user()->id);
    }

    private function find(string $id): CurrentAccount
    {
        abort_unless(ctype_digit($id), 404);

        return $this->currentAccounts()->findOrFail($id);
    }

    private function assign(CurrentAccount $entry, Request $request): array
    {
        $data = (array) $request->input('current_account', []);
        $integer = fn ($name) => isset($data[$name]) && preg_match('/^-?\d+$/', trim((string) $data[$name])) ? (int) $data[$name] : null;

        $entry->cash_account_id = $integer('cash_account_id');
        $entry->cost_id = $integer('cost_id');
        $entry->type_launche = CurrentAccount::TYPES[$data['type_launche'] ?? ''] ?? null;
        $entry->date_ocurrence = self::parseDate($data['date_ocurrence'] ?? null);
        $entry->priceText = trim((string) ($data['price'] ?? ''));
        $entry->price = is_numeric($entry->priceText) ? round((float) $entry->priceText, 2) : null;
        $entry->historic = ($data['historic'] ?? '') === '' ? null : (string) $data['historic'];

        return $entry->validate();
    }

    private function asJson(CurrentAccount $entry): array
    {
        return [
            'id' => $entry->id,
            'cash_account_id' => $entry->cash_account_id,
            'cost_id' => $entry->cost_id,
            'date_ocurrence' => $entry->date_ocurrence->toDateString(),
            'price' => Format::decimalText($entry->price),
            'historic' => $entry->historic,
            'user_id' => $entry->user_id,
            'type_launche' => $entry->type_launche,
            'url' => url("/current_accounts/{$entry->id}.json"),
        ];
    }

    public static function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }
}
