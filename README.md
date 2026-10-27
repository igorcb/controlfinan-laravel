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
- [Benchmark](#benchmark)
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

## Benchmark

O benchmark que compara as sete versões do ControlFinan (Rails, Django, Laravel, Go, Java, Rust e C#), com os mesmos dados e os mesmos limites de CPU e memória, fica no repositório do app de referência: [controlfinan-rails8-hotwire/benchmark](https://github.com/igorcb/controlfinan-rails8-hotwire/tree/main/benchmark).

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
tests/                  Pest
```

## Licença

[MIT](LICENSE)
