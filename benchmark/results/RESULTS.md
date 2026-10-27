# Benchmark: ControlFinan em Rails x Django x Laravel FPM x Laravel Octane

Mesmo app (paridade de telas e regras), mesmos dados, modo produção.
Puma e Gunicorn: 2 processos × 5 threads (com GVL/GIL, até 2 requisições usam CPU ao mesmo tempo); PHP-FPM e Octane: 2 workers, um por CPU.
Cada app limitado a 2 CPUs e 1 GB de RAM, com o seu Postgres 16 (2 CPUs, 1 GB).
Carga: k6 com 20 usuários simultâneos, 10 s de aquecimento + 30 s medidos por tela, logado de verdade.

Versões: Rails 8.1 + Hotwire (Puma); Django 5.2 (Gunicorn); Laravel 12 (PHP-FPM + nginx); Laravel 12 + Octane (FrankenPHP).

## Requisições por segundo e latência

| Tela | App | req/s | p50 (ms) | p95 (ms) | p99 (ms) | erros | CPU média | memória pico |
|---|---|---:|---:|---:|---:|---:|---:|---:|
| Conta Corrente (30 itens + saldo) | Rails | 101 | 178.0 | 311.8 | 338.1 | 0.0% | 192% | 264 MiB |
| Conta Corrente (30 itens + saldo) | Django | 76 | 245.2 | 431.4 | 740.8 | 0.0% | 196% | 103 MiB |
| Conta Corrente (30 itens + saldo) | Laravel FPM | 112 | 175.4 | 203.4 | 227.3 | 0.0% | 128% | 39 MiB |
| Conta Corrente (30 itens + saldo) | Laravel Octane | 115 | 171.8 | 199.0 | 210.9 | 0.0% | 144% | 101 MiB |
| Saldo do dia (filtro) | Rails | 236 | 81.6 | 112.3 | 166.3 | 0.0% | 194% | 273 MiB |
| Saldo do dia (filtro) | Django | 140 | 118.5 | 373.9 | 492.0 | 0.0% | 198% | 103 MiB |
| Saldo do dia (filtro) | Laravel FPM | 181 | 106.9 | 139.5 | 179.1 | 0.0% | 155% | 39 MiB |
| Saldo do dia (filtro) | Laravel Octane | 221 | 89.9 | 103.2 | 112.2 | 0.0% | 171% | 102 MiB |
| Dashboard do mês | Rails | 160 | 109.3 | 212.7 | 224.9 | 0.0% | 194% | 276 MiB |
| Dashboard do mês | Django | 154 | 126.6 | 156.8 | 297.4 | 0.0% | 197% | 106 MiB |
| Dashboard do mês | Laravel FPM | 175 | 110.4 | 137.8 | 175.8 | 0.0% | 146% | 39 MiB |
| Dashboard do mês | Laravel Octane | 220 | 90.2 | 102.9 | 112.8 | 0.0% | 154% | 102 MiB |
| API JSON (30 itens) | Rails | 315 | 62.8 | 75.1 | 82.7 | 0.0% | 189% | 297 MiB |
| API JSON (30 itens) | Django | 213 | 86.6 | 193.9 | 401.9 | 0.0% | 198% | 106 MiB |
| API JSON (30 itens) | Laravel FPM | 160 | 121.0 | 169.4 | 206.6 | 0.0% | 176% | 39 MiB |
| API JSON (30 itens) | Laravel Octane | 210 | 94.1 | 108.7 | 120.8 | 0.0% | 180% | 102 MiB |
| Criar lançamento (POST) | Rails | 404 | 48.7 | 55.4 | 59.5 | 0.0% | 204% | 308 MiB |
| Criar lançamento (POST) | Django | 314 | 55.1 | 129.6 | 179.5 | 0.0% | 199% | 106 MiB |
| Criar lançamento (POST) | Laravel FPM | 195 | 101.1 | 118.0 | 131.2 | 0.0% | 163% | 39 MiB |
| Criar lançamento (POST) | Laravel Octane | 307 | 64.9 | 72.5 | 77.7 | 0.0% | 174% | 102 MiB |

## Quem foi mais rápido (req/s)

| Tela | Mais rápido | Ordem |
|---|---|---|
| Conta Corrente (30 itens + saldo) | Laravel Octane (1.0x Laravel FPM, 1.1x Rails, 1.5x Django) | Laravel Octane > Laravel FPM > Rails > Django |
| Saldo do dia (filtro) | Rails (1.1x Laravel Octane, 1.3x Laravel FPM, 1.7x Django) | Rails > Laravel Octane > Laravel FPM > Django |
| Dashboard do mês | Laravel Octane (1.3x Laravel FPM, 1.4x Rails, 1.4x Django) | Laravel Octane > Laravel FPM > Rails > Django |
| API JSON (30 itens) | Rails (1.5x Django, 1.5x Laravel Octane, 2.0x Laravel FPM) | Rails > Django > Laravel Octane > Laravel FPM |
| Criar lançamento (POST) | Rails (1.3x Django, 1.3x Laravel Octane, 2.1x Laravel FPM) | Rails > Django > Laravel Octane > Laravel FPM |

Vitórias: Rails 3, Django 0, Laravel FPM 0, Laravel Octane 2.

## Boot, memória e imagem

| | Rails | Django | Laravel FPM | Laravel Octane |
|---|---:|---:|---:|---:|
| Subir até responder `/up` (inclui migrations) | 5.3 s | 2.7 s | 1.8 s | 2.0 s |
| Memória parado | 125 MiB | 82 MiB | 32 MiB | 81 MiB |
| Tamanho da imagem Docker | 515 MB | 168 MB | 125 MB | 224 MB |

## Código

| | Rails | Django | Laravel FPM | Laravel Octane |
|---|---:|---:|---:|---:|
| Linhas de código do app (sem linhas em branco) | 787 | 1039 | 1116 | 1116 |
| Linhas de teste | 0 | 392 | 338 | 338 |
