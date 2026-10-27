@php($c = $currentAccount)
<form class="card space-y-4" action="{{ $c->exists ? '/current_accounts/'.$c->id : '/current_accounts' }}" method="post">@if ($c->exists) @method('PATCH') @endif @csrf
  @include('current_accounts._errors')

  <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Tipo">
    <label class="flex cursor-pointer items-center justify-center rounded-md border border-slate-300 py-3 font-medium text-slate-600 has-checked:border-red-600 has-checked:bg-red-600 has-checked:text-white">
      <input class="sr-only" type="radio" value="debito"@if ($c->type_launche === -1) checked="checked"@endif name="current_account[type_launche]" id="current_account_type_launche_debito"> Débito
    </label>
    <label class="flex cursor-pointer items-center justify-center rounded-md border border-slate-300 py-3 font-medium text-slate-600 has-checked:border-emerald-600 has-checked:bg-emerald-600 has-checked:text-white">
      <input class="sr-only" type="radio" value="credito"@if ($c->type_launche === 1) checked="checked"@endif name="current_account[type_launche]" id="current_account_type_launche_credito"> Crédito
    </label>
  </div>

  <div class="grid gap-4 sm:grid-cols-2">
    <div>
      <label class="label" for="current_account_price">Valor</label>
      <input step="0.01" min="0.01" placeholder="0,00" inputmode="decimal"@unless ($c->exists) autofocus="autofocus"@endunless class="input text-lg font-semibold" type="number" value="{{ $c->price !== null ? \App\Support\Format::decimalText($c->price) : $c->priceText }}" name="current_account[price]" id="current_account_price">
    </div>

    <div>
      <label class="label" for="current_account_date_ocurrence">Data</label>
      <input class="input" type="date" value="{{ $c->date_ocurrence?->toDateString() }}" name="current_account[date_ocurrence]" id="current_account_date_ocurrence">
    </div>

    <div>
      <label class="label" for="current_account_cost_id">Categoria</label>
      <select class="input" name="current_account[cost_id]" id="current_account_cost_id">@if ($c->cost_id === null)<option value="">Selecione</option>@endif
        @foreach (\App\Models\CurrentAccount::COSTS as $id => $name)<option{!! $c->cost_id === $id ? ' selected="selected"' : '' !!} value="{{ $id }}">{{ $name }}</option>@endforeach
      </select>
    </div>

    <div>
      <label class="label" for="current_account_cash_account_id">Conta</label>
      <select class="input" name="current_account[cash_account_id]" id="current_account_cash_account_id">@if ($c->cash_account_id === null)<option value="">Selecione</option>@endif
        @foreach (\App\Models\CurrentAccount::CASH_ACCOUNTS as $id => $name)<option{!! $c->cash_account_id === $id ? ' selected="selected"' : '' !!} value="{{ $id }}">{{ $name }}</option>@endforeach
      </select>
    </div>

    <div class="sm:col-span-2">
      <label class="label" for="current_account_historic">Histórico</label>
      <input class="input" type="text" value="{{ $c->historic }}" name="current_account[historic]" id="current_account_historic">
    </div>
  </div>

  <div class="flex flex-col-reverse gap-3 sm:flex-row">
    <a class="btn-secondary py-3 sm:py-2" href="{{ $c->exists ? '/current_accounts/'.$c->id : '/current_accounts' }}">Cancelar</a>
    <input type="submit" value="Salvar" class="btn-primary py-3 sm:order-first sm:py-2">
  </div>
</form>
