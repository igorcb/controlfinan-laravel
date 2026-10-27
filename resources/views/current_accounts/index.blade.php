@extends('layouts.app')
@section('title', 'Conta Corrente')
@section('content')
@php($F = \App\Support\Format::class)
<div class="mb-4 flex items-end justify-between gap-4">
  <div>
    <h1 class="text-2xl font-semibold">Conta Corrente</h1>
    <p class="mt-1 text-sm text-slate-600">{{ $date ? 'Saldo do dia '.$date->format('d/m/Y') : 'Saldo total' }}</p>
    <p class="text-2xl font-bold {{ $F::amountClass($balance) }}">{{ $F::currency($balance) }}</p>
  </div>

  <a class="btn-primary hidden sm:inline-flex" href="/current_accounts/new">Novo lançamento</a>
</div>

<form class="mb-4 flex items-end gap-2" action="/current_accounts" accept-charset="UTF-8" method="get">
  <div class="min-w-0 flex-1 sm:flex-none">
    <label class="label" for="date">Consultar saldo do dia</label>
    <input class="input" type="date" value="{{ $date?->toDateString() }}" name="date" id="date">
  </div>
  <input type="submit" value="Consultar" class="btn-secondary">
  @if ($date)<a class="btn-secondary" href="/current_accounts">Limpar</a>@endif
</form>

@if ($currentAccounts->isEmpty())
  <p class="card text-center text-sm text-slate-500">Nenhum lançamento encontrado.</p>
@else
  {{-- Mobile: cards --}}
  <ul class="divide-y divide-slate-100 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm sm:hidden">
    @foreach ($currentAccounts as $entry)
      <li>
        <a class="flex items-center justify-between gap-3 px-4 py-3 active:bg-slate-50" href="/current_accounts/{{ $entry->id }}">
          <div class="min-w-0">
            <p class="truncate font-medium">{{ $entry->nameCost() }}</p>
            <p class="truncate text-xs text-slate-500">
              {{ $entry->date_ocurrence->format('d/m') }} · {{ $entry->nameAccount() }}{{ $entry->historic ? ' · '.$entry->historic : '' }}
            </p>
          </div>
          <span class="shrink-0 font-semibold {{ $entry->isCredit() ? 'text-emerald-700' : 'text-red-700' }}">
            {{ $F::currency($entry->signedPrice()) }}
          </span>
        </a>
      </li>
    @endforeach
  </ul>

  {{-- Desktop: table --}}
  <div class="hidden overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm sm:block">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
      <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
        <tr>
          <th class="px-4 py-3">Data</th>
          <th class="px-4 py-3">Conta</th>
          <th class="px-4 py-3">Categoria</th>
          <th class="px-4 py-3">Histórico</th>
          <th class="px-4 py-3 text-right">Valor</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach ($currentAccounts as $entry)
          <tr id="current_account_{{ $entry->id }}" class="hover:bg-slate-50">
            <td class="whitespace-nowrap px-4 py-3">{{ $entry->date_ocurrence->format('d/m/Y') }}</td>
            <td class="px-4 py-3">{{ $entry->nameAccount() }}</td>
            <td class="px-4 py-3">{{ $entry->nameCost() }}</td>
            <td class="px-4 py-3 text-slate-600">{{ $entry->historic }}</td>
            <td class="whitespace-nowrap px-4 py-3 text-right font-medium {{ $entry->isCredit() ? 'text-emerald-700' : 'text-red-700' }}">
              {{ $F::currency($entry->signedPrice()) }}
            </td>
            <td class="whitespace-nowrap px-4 py-3 text-right">
              <a class="link" href="/current_accounts/{{ $entry->id }}">Ver</a>
              <a class="link ml-2" href="/current_accounts/{{ $entry->id }}/edit">Editar</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

@php($dateParam = $date ? 'date='.$date->toDateString().'&' : '')
<nav class="mt-4 flex justify-between gap-3">
  @if ($page > 1)<a class="btn-secondary" href="/current_accounts?{{ $dateParam }}page={{ $page - 1 }}">← Mais recentes</a>@endif
  @if ($nextPage)<a class="btn-secondary ml-auto" href="/current_accounts?{{ $dateParam }}page={{ $page + 1 }}">Mais antigos →</a>@endif
</nav>
@endsection
