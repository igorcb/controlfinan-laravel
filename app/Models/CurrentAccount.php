<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrentAccount extends Model
{
    public const CASH_ACCOUNTS = [
        0 => 'Conta Dinheiro',
        1 => 'Itaú',
        2 => 'Santander',
        3 => 'CEF Poupança',
        4 => 'PagSeguro',
        5 => 'Uber',
        6 => 'Platinum Itaú',
        7 => 'Banco do Brasil',
        8 => '99Pop',
    ];

    public const COSTS = [
        0 => 'Combustível',
        1 => 'Central',
        2 => 'Aluguel Carro',
        3 => 'Almoço',
        4 => 'Lanche',
        5 => 'Diversos',
        6 => 'Corridas',
        7 => 'Telefone',
        8 => 'Lavagem',
        9 => 'Transferência',
        10 => 'Depósitos',
        11 => 'Estacionamento',
        12 => 'Impostos',
        13 => 'Multas',
        14 => 'Manutenção',
        15 => 'Viagens Uber',
    ];

    public const TYPES = ['debito' => -1, 'credito' => 1];

    public const RIDES = 6;

    // Ride expenses discounted from rides in the monthly balance (legacy dashboard rule).
    public const RIDE_EXPENSES = [0 => 'Combustível', 8 => 'Lavagem', 7 => 'Telefone'];

    protected $guarded = ['id', 'user_id'];

    protected $casts = [
        'cash_account_id' => 'integer',
        'cost_id' => 'integer',
        'type_launche' => 'integer',
        'date_ocurrence' => 'immutable_date',
        'price' => 'decimal:2',
    ];

    /** Raw price text from the form, used by validate() like Rails numericality. */
    public ?string $priceText = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('date_ocurrence')->orderByDesc('id');
    }

    public function scopeOnDate(Builder $query, CarbonImmutable $day): Builder
    {
        return $query->where('date_ocurrence', $day->toDateString());
    }

    public function scopeInMonth(Builder $query, CarbonImmutable $month): Builder
    {
        return $query->whereBetween('date_ocurrence', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()]);
    }

    public function scopeRides(Builder $query): Builder
    {
        return $query->where('cost_id', self::RIDES);
    }

    // Credits minus debits: the sign comes from type_launche (-1 or 1).
    public static function balanceOf(Builder $query): string
    {
        return (string) ((clone $query)->reorder()->sum(\DB::raw('price * type_launche')) ?: '0');
    }

    /** [Y-m-d => balance], most recent first. */
    public static function balanceByDay(Builder $query): array
    {
        return (clone $query)->reorder()
            ->selectRaw('date_ocurrence, SUM(price * type_launche) AS total')
            ->groupBy('date_ocurrence')->orderByDesc('date_ocurrence')
            ->toBase()->pluck('total', 'date_ocurrence')->all();
    }

    /** [cost_id => sum of price], only for the given categories. */
    public static function totalByCost(Builder $query, array $costIds): array
    {
        return (clone $query)->reorder()->whereIn('cost_id', $costIds)
            ->selectRaw('cost_id, SUM(price) AS total')->groupBy('cost_id')
            ->toBase()->pluck('total', 'cost_id')->all();
    }

    public function isCredit(): bool
    {
        return $this->type_launche === 1;
    }

    public function nameAccount(): string
    {
        return self::CASH_ACCOUNTS[$this->cash_account_id] ?? 'Não definido';
    }

    public function nameCost(): string
    {
        return self::COSTS[$this->cost_id] ?? 'Não definido';
    }

    public function typeLauncheName(): string
    {
        return $this->isCredit() ? 'Crédito' : 'Débito';
    }

    public function signedPrice(): string
    {
        return $this->isCredit() ? (string) $this->price : bcmul((string) $this->price, '-1', 2);
    }

    /** pt-BR messages in the same order as the Rails validations. */
    public function validate(): array
    {
        $errors = [];
        if (! in_array($this->type_launche, self::TYPES, true)) {
            $errors[] = 'Tipo não está incluído na lista';
        }
        if (! array_key_exists($this->cash_account_id ?? -1, self::CASH_ACCOUNTS) || $this->cash_account_id === null) {
            $errors[] = 'Conta não está incluído na lista';
        }
        if (! array_key_exists($this->cost_id ?? -1, self::COSTS) || $this->cost_id === null) {
            $errors[] = 'Categoria não está incluído na lista';
        }
        if (! $this->date_ocurrence) {
            $errors[] = 'Data não pode ficar em branco';
        }
        // Like Rails presence + numericality: a blank value is also "not a number".
        if ($this->price === null && ($this->priceText ?? '') === '') {
            array_push($errors, 'Valor não pode ficar em branco', 'Valor não é um número');
        } elseif ($this->price === null) {
            $errors[] = 'Valor não é um número';
        } elseif (bccomp((string) $this->price, '0', 2) <= 0) {
            $errors[] = 'Valor deve ser maior que 0';
        }

        return $errors;
    }
}
