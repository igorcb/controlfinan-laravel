@extends('layouts.app')
@section('title', 'Recuperar senha')
@section('content')
<div class="mx-auto max-w-sm">
  <h1 class="mb-6 text-2xl font-semibold">Esqueceu a senha?</h1>

  <form class="card space-y-4" action="/passwords" accept-charset="UTF-8" method="post">@csrf
    <div>
      <label class="label" for="email_address">E-mail</label>
      <input required="required" autofocus="autofocus" autocomplete="username" value="{{ $emailAddress }}" class="input" type="email" name="email_address" id="email_address">
    </div>
    <input type="submit" value="Enviar instruções" class="btn-primary w-full">
  </form>

  <p class="mt-4 text-sm"><a class="link" href="/session/new">Voltar para o login</a></p>
</div>
@endsection
