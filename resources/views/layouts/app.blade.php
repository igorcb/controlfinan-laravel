<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <title>@yield('title', 'ControlFinan')</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="application-name" content="ControlFinan">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#047857">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="/icon.png" type="image/png">
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/icon.png">

    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js" defer></script>
  </head>

  <body class="min-h-screen bg-slate-50 text-slate-900">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white">
      <nav class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
        <a class="text-lg font-semibold text-emerald-700" href="/">ControlFinan</a>
        @if ($currentUser)
          <div class="flex items-center gap-4 text-sm">
            <a class="link hidden sm:inline" href="/current_accounts">Conta Corrente</a>
            <a class="link hidden sm:inline" href="/dashboard">Dashboard</a>
            <span class="hidden text-slate-500 md:inline">{{ $currentUser->email_address }}</span>
            <form class="button_to" method="post" action="/session">@method('DELETE')@csrf<button class="link py-1" type="submit">Sair</button></form>
          </div>
        @endif
      </nav>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6 {{ $currentUser ? 'pb-28 sm:pb-6' : '' }}">
      @if (session('notice'))
        <p class="mb-4 rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('notice') }}</p>
      @endif
      @if (session('alert'))
        <p class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('alert') }}</p>
      @endif

      @yield('content')
    </main>

    @if ($currentUser)
      {{-- Mobile bottom navigation --}}
      <nav class="fixed inset-x-0 bottom-0 z-10 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)] sm:hidden">
        <div class="grid grid-cols-3 items-center text-xs font-medium text-slate-600">
          <a class="flex flex-col items-center gap-1 py-2 {{ ($nav ?? null) === 'index' ? 'text-emerald-700' : '' }}" href="/current_accounts">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm0 5.25h.007v.008H3.75V12Zm0 5.25h.007v.008H3.75v-.008Z"/></svg>
            Lançamentos
          </a>
          <a class="mx-auto -mt-6 flex size-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-lg ring-4 ring-white" aria-label="Novo lançamento" href="/current_accounts/new">
            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
          </a>
          <a class="flex flex-col items-center gap-1 py-2 {{ ($nav ?? null) === 'dashboard' ? 'text-emerald-700' : '' }}" href="/dashboard">
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
            Dashboard
          </a>
        </div>
      </nav>
    @endif
  </body>
</html>
