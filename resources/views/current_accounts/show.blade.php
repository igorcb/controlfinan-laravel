@extends('layouts.app')
@section('title', 'Lançamento')
@section('content')
@php($c = $currentAccount)
<h1 class="mb-6 text-2xl font-semibold">Lançamento</h1>

<div class="card">
  <dl class="grid gap-4 text-sm sm:grid-cols-2">
    <div>
      <dt class="text-slate-500">Data</dt>
      <dd class="font-medium">{{ $c->date_ocurrence->format('d/m/Y') }}</dd>
    </div>
    <div>
      <dt class="text-slate-500">Tipo</dt>
      <dd class="font-medium">{{ $c->typeLauncheName() }}</dd>
    </div>
    <div>
      <dt class="text-slate-500">Conta</dt>
      <dd class="font-medium">{{ $c->nameAccount() }}</dd>
    </div>
    <div>
      <dt class="text-slate-500">Categoria</dt>
      <dd class="font-medium">{{ $c->nameCost() }}</dd>
    </div>
    <div>
      <dt class="text-slate-500">Valor</dt>
      <dd class="font-medium {{ $c->isCredit() ? 'text-emerald-700' : 'text-red-700' }}">{{ \App\Support\Format::currency($c->signedPrice()) }}</dd>
    </div>
    <div>
      <dt class="text-slate-500">Histórico</dt>
      <dd class="font-medium">{{ $c->historic ?: '—' }}</dd>
    </div>
  </dl>

  <div class="mt-6 flex flex-wrap gap-3">
    <a class="btn-primary" href="/current_accounts/{{ $c->id }}/edit">Editar</a>
    <a class="btn-secondary" href="/current_accounts">Voltar</a>
    <form class="button_to" method="post" action="/current_accounts/{{ $c->id }}" data-confirm="Excluir este lançamento?">@method('DELETE')@csrf<button class="btn-danger" type="submit">Excluir</button></form>
  </div>
</div>
@endsection
