# ControlFinan (Laravel)

![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind-4-06B6D4?logo=tailwindcss&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)

Aplicação web de controle financeiro pessoal, pensada para quem trabalha como motorista de aplicativo. Cada usuário registra entradas (créditos) e saídas (débitos) em contas como dinheiro, bancos e carteiras de aplicativos, e acompanha o saldo por dia e por mês.

Esta é a versão em **Laravel** do [ControlFinan em Rails 8 + Hotwire](https://github.com/igorcb/controlfinan-rails8-hotwire). Ela tem **paridade de funcionalidades e de visual** com o original, assim como a [versão em Django](https://github.com/igorcb/controlfinan-django): mesmas telas, mesmas regras, mesmas mensagens e **mesmo esquema de banco**. As versões podem usar os mesmos dados, inclusive as senhas.

## Sumário

- [Funcionalidades](#funcionalidades)
- [Como a paridade foi feita](#como-a-paridade-foi-feita)
- [Benchmark: Rails x Django x Laravel](#benchmark-rails-x-django-x-laravel)
- [Como rodar com Docker](#como-rodar-com-docker)
- [Testes](#testes)
- [Estrutura](#estrutura)
- [Licença](#licença)

## Funcionalidades

| Tela | Rota | O que faz |
|---|---|---|
| Conta Corrente | `/` | Lista os lançamentos, do mais recente para o mais antigo, 30 por página, com o saldo total |
| Saldo do dia | `/current_accounts?date=AAAA-MM-DD` | Filtra a listagem por um dia e mostra o saldo daquele dia |
| Novo lançamento | `/current_accounts/new` | Cadastra um crédito ou um débito |
| Detalhe, edição e exclusão | `/current_accounts/:id` | Mostra, altera ou remove um lançamento |
| Dashboard | `/dashboard` | Abas **Saldo Diário** (saldo de cada dia do mês) e **Saldo Mensal** (corridas menos combustível, lavagem e telefone). As setas navegam entre os meses |
| JSON | `/current_accounts.json` e `/current_accounts/:id.json` | Os mesmos dados, para integrações |
| Cadastro, login e senha | `/registration/new`, `/session/new`, `/passwords/new` | Conta de usuário, com senha de no mínimo 6 caracteres |

Cada usuário só enxerga os próprios lançamentos.

## Como a paridade foi feita

| Rails 8 + Hotwire | Laravel |
|---|---|
| Autenticação nativa do Rails 8 (`has_secure_password`, tabela `sessions`, cookie `session_id`) | Mesmo modelo: tabela `sessions` e cookie criptografado `session_id`. As senhas usam **bcrypt no formato do Rails** (`$2a$`) |
| `rate_limit to: 10, within: 3.minutes` | Middleware `rate_limit`: 10 tentativas a cada 3 minutos por IP |
| Token de redefinição de senha (15 minutos) | Token criptografado com validade de 15 minutos, invalidado quando a senha muda |
| Turbo (`data-turbo-confirm`) e Stimulus (abas) | JavaScript simples para a confirmação e as abas |
| jbuilder | JSON compacto com os mesmos campos e o mesmo formato (por exemplo, `"price":"150.0"`) |
| Views ERB e `form_with` | Views Blade que geram o mesmo HTML, inclusive os `id`, `name` e o campo `_method` |
| Tailwind CSS 4 | Tailwind CSS 4 (CLI standalone), com as mesmas classes |

**Verificação**, com o mesmo usuário e os mesmos dados (7.292 lançamentos) nos dois apps:

- Texto idêntico em 15 telas e respostas, incluindo paginação, filtro por dia, dashboard, formulários, mensagens de validação e erros de cadastro.
- JSON idêntico byte a byte (lista e detalhe).
- **14 telas idênticas pixel a pixel**: login, lista, filtro, dashboard, novo, detalhe e edição, no computador (1280 px) e no celular (390 px).

## Benchmark: Rails x Django x Laravel

Mesmo app, mesmos dados e todos em **modo produção**:

- **Rails 8.1** com Puma (Ruby 3.4 + YJIT) e **Django 5.2** com Gunicorn `gthread` (Python 3.12): 2 processos × 5 threads cada.
- **Laravel 12** com **Octane + FrankenPHP** (PHP 8.4 + OPcache): o app fica na memória entre requisições, como no Puma e no Gunicorn. São 2 workers (um por CPU) com conexões persistentes ao banco.
- Cada app limitado a **2 CPUs e 1 GB de RAM**, cada um com o seu Postgres 16 (2 CPUs, 1 GB).
- Carga com **k6**: 20 usuários simultâneos, logados de verdade pelo formulário de login, 10 s de aquecimento e 30 s medidos por tela.

### Requisições por segundo (mais é melhor)

| Tela | Rails | Django | Laravel |
|---|---:|---:|---:|
| Conta Corrente (30 itens + saldo) | 101 | 76 | **115** |
| Saldo do dia (filtro) | **236** | 140 | 221 |
| Dashboard do mês | 160 | 154 | **220** |
| API JSON (30 itens) | **315** | 213 | 210 |
| Criar lançamento (POST) | **404** | 314 | 307 |

Nenhuma requisição falhou. Latências, CPU e memória de cada tela estão em [benchmark/results/RESULTS.md](benchmark/results/RESULTS.md).

### Recursos

| | Rails | Django | Laravel |
|---|---:|---:|---:|
| Subir até responder `/up` (inclui migrations) | 5,3 s | 2,7 s | **2,0 s** |
| Memória parado | 125 MiB | 82 MiB | **81 MiB** |
| Memória no pico da carga | ~300 MiB | ~106 MiB | **~102 MiB** |
| Tamanho da imagem Docker | 515 MB | **168 MB** | 224 MB |

### O que os números mostram

- **Rails venceu 3 de 5 telas**, com folga nas mais leves (JSON e POST), em que o custo fixo de cada requisição pesa mais.
- **Laravel venceu 2 de 5** (lista e dashboard) e ficou perto do Rails no filtro por dia, usando um terço da memória do Rails.
- **Django** ficou atrás nas telas, mas é o mais leve em imagem e quase empata com o Laravel em memória.

### Lições de medição

Os primeiros números do Laravel estavam errados, de duas formas:

1. **Conexão com o banco a cada requisição.** No PHP-FPM, cada requisição abria uma conexão nova com o Postgres, e a autenticação SCRAM custava ~15 ms de CPU. Rails e Django reaproveitam conexões. A correção foi usar conexão persistente (`DB_PERSISTENT=true`). Depois o app passou a usar o Octane, que mantém a conexão aberta.
2. **Workers demais para a CPU.** Com 10 workers em 2 CPUs, como os "2 × 5" do Rails e do Django, o Laravel caiu para ~50 req/s. O CPU por requisição subiu de ~9 ms para ~32 ms com o container sendo estrangulado pelo limite de CPU. No Ruby e no Python isso não acontece: o GVL/GIL deixa só 2 requisições usarem CPU ao mesmo tempo. Com **2 workers** (um por CPU, o padrão do Octane), o mesmo app foi 4 vezes mais rápido.

O Django também teve a sua lição: o primeiro resultado do Rails saiu errado por causa de um token CSRF no script de teste. **Meça, desconfie e confira.**

### Como reproduzir

```bash
# Requisitos: este repositório, ../controlfinan-rails8-hotwire (com o banco de desenvolvimento no ar,
# que fornece os dados) e ../controlfinan-django.
./benchmark/run.sh           # os três: rails django laravel
./benchmark/run.sh laravel   # só alguns; os resultados dos outros são mantidos
```

O relatório é gravado em `benchmark/results/RESULTS.md`.

## Como rodar com Docker

```bash
cp .env.example .env            # e ajuste APP_KEY (php artisan key:generate) e o banco, se quiser
docker compose up               # app em http://localhost:8002
```

O container de desenvolvimento instala as dependências, compila o Tailwind em modo watch, roda as migrations e sobe o servidor.

Imagem de produção: `Dockerfile.prod`, com Laravel Octane + FrankenPHP, OPcache e caches de config, rotas e views. Configure `APP_KEY`, `DB_*` e `OCTANE_WORKERS` (padrão 2).

## Testes

41 testes com **Pest** (unitários e de feature) cobrindo login, cadastro, redefinição de senha, limite de tentativas, lançamentos (CRUD, filtro, paginação, isolamento entre usuários e JSON), dashboard e formatação.

```bash
docker compose exec web ./vendor/bin/pest
```

Os testes usam o banco `controlfinan_laravel_test` no Postgres do `docker compose`.

## Estrutura

```
app/
  Http/Controllers/     CurrentAccounts, Dashboards, Sessions, Registrations, Passwords
  Http/Middleware/      Authenticate (sessão no estilo Rails 8) e RateLimit
  Models/               User, Session, CurrentAccount
  Support/              Auth (cookie session_id) e Format (moeda e decimais como no Rails)
resources/views/        Blade com o mesmo HTML do app Rails
benchmark/              docker-compose, k6, run.sh e report.py
tests/                  Pest
```

## Licença

[MIT](LICENSE)
