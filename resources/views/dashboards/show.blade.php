@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
  <div>
    <h1 class="text-2xl font-semibold">Dashboard</h1>
    <div class="mt-2 flex items-center gap-3 text-sm">
      <a class="btn-secondary px-3 py-1" aria-label="Mês anterior" href="/dashboard?month={{ $previousMonth }}">←</a>
      <span class="font-medium">{{ $monthLabel }}</span>
      <a class="btn-secondary px-3 py-1" aria-label="Próximo mês" href="/dashboard?month={{ $nextMonth }}">→</a>
    </div>
  </div>

  <div class="flex w-full flex-wrap items-end gap-3 sm:w-auto">
    <form class="flex flex-1 items-end gap-2" action="/current_accounts" accept-charset="UTF-8" method="get">
      <div class="min-w-0 flex-1">
        <label class="label" for="date">Consultar saldo diário</label>
        <input class="input" type="date" value="{{ $today }}" name="date" id="date">
      </div>
      <input type="submit" value="Consultar" class="btn-secondary">
    </form>
    <a class="btn-primary hidden sm:inline-flex" href="/current_accounts/new">Novo lançamento</a>
  </div>
</div>

<div data-tabs class="card p-4 sm:p-6">
  <div role="tablist" class="-mx-2 mb-4 grid grid-cols-2 gap-2 border-b border-slate-200 px-2 pb-3 sm:mx-0 sm:flex sm:px-0">
    @foreach (['Saldo Diário', 'Saldo Mensal'] as $label)
      <button type="button" role="tab" data-tab
              class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 aria-selected:bg-emerald-600 aria-selected:text-white">
        {{ $label }}
      </button>
    @endforeach
  </div>

  <div data-panel>
    @include('dashboards._amounts_table', ['header' => 'Data', 'rows' => $dailyRows, 'totalLabel' => 'Total do mês', 'total' => $monthBalance])
  </div>

  <div data-panel hidden>
    @include('dashboards._amounts_table', ['header' => 'Item', 'rows' => $monthlyRows, 'totalLabel' => 'Saldo do mês', 'total' => $ridesNet])
  </div>
</div>
@endsection
