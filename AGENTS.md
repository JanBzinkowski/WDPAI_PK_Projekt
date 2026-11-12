# AGENTS.md — Single Source of Truth dla agentów AI

> Ten plik jest **głównym źródłem prawdy** o projekcie dla każdego agenta AI (Antigravity, Copilot, Claude, Cursor itp.).
> Przeczytaj go **w całości przed rozpoczęciem pracy** i **zaktualizuj go po jej zakończeniu**.

---

## 🔁 OBOWIĄZKOWY PROMPT DLA AGENTA (czytaj przy każdym zadaniu)

```text
Po każdej zmianie w projekcie MUSISZ zaktualizować AGENTS.md:
1. Dodałeś / usunąłeś / przeniosłeś plik lub katalog  -> zaktualizuj sekcję "Struktura projektu".
2. Dodałeś trasę                                      -> dopisz ją do tabeli "Routing".
3. Dodałeś tabelę / kolumnę / FK / widok / trigger     -> zaktualizuj "Baza danych" (i ERD w README.md).
4. Zaimplementowałeś funkcjonalność                    -> dopisz wiersz w "Rejestr zaimplementowanych funkcjonalności"
                                                         i zmień status kryterium w "Checklista kryteriów".
5. Dodałeś zmienną środowiskową                        -> dopisz ją do "Zmienne środowiskowe" ORAZ do .env.example.
6. Dodałeś klucz tłumaczenia / nową usługę / komendę   -> zaktualizuj odpowiednią sekcję.
7. Proponując commit                                   -> użyj formatu z sekcji "Konwencja commitów"
                                                         (prefiks: opis).
Nie usuwaj cudzych wpisów. Wpisy dopisuj w formacie istniejących tabel. Data w formacie DD-MM-RRRR.
```

---

## 1. Opis projektu

- **Typ:** aplikacja webowa (projekt zaliczeniowy WDPAI).
- **Temat:** Aplikacja do powtarzania fiszek oparta o ststem leitnera – uzupełnić (w konfiguracji PHP występuje nazwa `mealplanner`).
- **Stos:** PHP 8.3 (FPM, bez frameworka) · Nginx · PostgreSQL 16 · Vanilla JS (Fetch API) · CSS · Docker Compose · Mailpit · pgAdmin.

## 2. Warunek konieczny: OBIEKTOWOŚĆ BACKENDU ⚠️

Każdy kod backendu **musi** być obiektowy. Zasady, których agent nie może łamać:

1. `declare(strict_types=1);` w każdym pliku PHP.
2. Pełne typowanie: parametry, typy zwracane, właściwości (`private`/`protected`/`readonly`).
3. **Kod proceduralny tylko w punkcie wejścia** (`public/index.php`), konfiguracji i skryptach CLI (cron) — i tam wyłącznie wywołuje obiekty.
4. Podział odpowiedzialności (warstwy, zależności tylko w dół):
   ```
   Router -> Controller -> Service -> Repository (interfejs) -> Database (PDO)
                 |                          |
               View                       Model / Entity
   ```
   - **Controller** — obsługa żądania, walidacja wejścia, wybór widoku / JSON. **Bez SQL i bez HTML.**
   - **Service** — logika biznesowa.
   - **Repository** — jedyne miejsce z SQL; zapytania wyłącznie parametryzowane (PDO prepared statements); mapuje wiersze na obiekty modelu.
   - **Model/Entity** — klasy z enkapsulacją (prywatne pola, gettery, enumy np. `enum Role: string`).
   - **View** — szablony `*.html.php`, wyjście zawsze przez `htmlspecialchars` (helper `e()`).
5. Współpraca przez **interfejsy** (`interface XRepositoryInterface`), wstrzykiwanie zależności przez konstruktor.
6. Kontrolery dziedziczą po `AppController`; klasy końcowe oznaczaj `final`.
7. Błędy: wyjątki (własne klasy, np. `NotFoundException`, `ValidationException`) → kontrolowane odpowiedzi 400/401/403/404/500.
8. Brak duplikacji (DRY): wspólne zapytania, walidacje, fragmenty HTML wydzielaj.
9. Sekrety wyłącznie z `getenv()` / `.env` — nigdy w kodzie.

## 3. Struktura projektu

> Aktualizuj po każdej zmianie plików. Zaznacz `(planowane)` dla elementów jeszcze nieistniejących.

```
my-app/
├── .agents/                      # konfiguracja i procedury agentów AI
│   └── skills/                   # katalog ze skillami
│       ├── wdpai-architecture/SKILL.md
│       ├── wdpai-database/SKILL.md
│       ├── wdpai-frontend/SKILL.md
│       └── wdpai-security-auth/SKILL.md
├── AGENTS.md                     # ten plik – SSOT dla agentów AI
├── SKILLS.md                     # indeks i opis skilli oraz procedur technicznych
├── README.md                     # dokumentacja (wymagania z kryterium 1)
├── .gitignore
├── .env.example                  # wzór zmiennych środowiskowych
├── composer.json                 # konfiguracja Composer (PSR-4 App\ -> src/, PHPMailer, PHPUnit)
├── docker-compose.yaml           # usługi: server, php, db, mailpit, pgadmin-wdpai
├── Routing.php                   # klasa Routing: GET/POST, wzorce, parametry {id} (\d+)
├── docker/
│   ├── db/Dockerfile             # postgres:16-alpine; katalog montowany jako initdb.d
│   ├── nginx/Dockerfile          # nginx:stable-alpine
│   ├── nginx/nginx.conf          # root /app/public, fastcgi -> php:9000
│   └── php/Dockerfile            # php:8.3-fpm-alpine + pdo_pgsql, gd, zip, opcache, composer + instalacja zależności
├── public/                       # document root
│   ├── index.php                 # punkt wejścia: vendor/autoload.php + rejestracja tras + dispatch
│   └── views/
│       ├── home.html.php
│       ├── not-found.html.php
│       ├── project.html.php
│       └── projects.html.php
└── src/
    ├── controllers/
    │   ├── AppController.php     # App\Controller\AppController: abstrakcyjny kontroler bazowy, render()
    │   ├── DefaultController.php # App\Controller\DefaultController: index, notFound
    │   └── ProjectController.php # App\Controller\ProjectController: index, show(int $id)
    └── core/
        └── Config.php            # App\Core\Config: final readonly Config, typowane gettery z getenv()
```

Docelowe katalogi (tworzyć zgodnie z tym układem):
`src/models/`, `src/repositories/`, `src/services/`, `src/core/` (Database, Session, Auth, Translator, Request/Response), `src/exceptions/`,
`public/views/partials/` (header, nav, footer), `public/css/`, `public/js/`, `public/uploads/` (poza gitem),
`lang/pl.php`, `lang/en.php`, `database/schema.sql` (eksport), `cron/`, `tests/` (PHPUnit).

## 4. Routing

| Metoda | Ścieżka          | Kontroler::akcja              | Typ trasy           | Uwagi |
|--------|------------------|-------------------------------|---------------------|-------|
| GET    | `/`              | DefaultController::index      | statyczna           |       |
| GET    | `/projects`      | ProjectController::index      | statyczna           | TODO query param (np. `?q=`) |
| GET    | `/projects/{id}` | ProjectController::show       | path param (`\d+`)  | TODO 404 gdy brak rekordu |
| *      | brak dopasowania | DefaultController::notFound   | fallback            | HTTP 404 |

## 5. Uruchomienie

```bash
cp .env.example .env
docker compose up --build
```

| Usługa        | Port hosta (domyślny)   | Nazwa w sieci |
|---------------|-------------------------|---------------|
| server (nginx)| `${APP_PORT:-8080}`     | server        |
| php-fpm       | —                       | php:9000      |
| PostgreSQL    | `${POSTGRES_PORT:-5433}`| db:5432       |
| Mailpit       | `${MAILPIT_PORT:-8025}` | mailpit (SMTP 1025) |
| pgAdmin       | `${PGADMIN_PORT:-5050}` | pgadmin-wdpai |

## 6. Zmienne środowiskowe

| Zmienna | Opis |
|---------|------|
| `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_TIMEZONE`, `APP_PORT` | konfiguracja środowiska aplikacji, URL, strefa czasowa i port HTTP |
| `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` | połączenie PDO z bazą danych PostgreSQL w PHP |
| `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD`, `POSTGRES_PORT` | kontener bazy danych PostgreSQL |
| `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASSWORD`, `SMTP_ENCRYPTION`, `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME` | konfiguracja wysyłki e-mail (SMTP / Mailpit) |
| `PGADMIN_DEFAULT_EMAIL`, `PGADMIN_DEFAULT_PASSWORD`, `PGADMIN_PORT`, `PGADMIN_CONTAINER_NAME` | pgAdmin |
| `MAILPIT_PORT` | UI Mailpit |

## 7. Baza danych

- Schemat / eksport: _brak — utworzyć `database/schema.sql` (ładowany przez `docker-entrypoint-initdb.d`)._
- Tabele: _brak_.
- Widoki / triggery / funkcje / transakcje: _brak_.
- Zasada: każda zmiana schematu = aktualizacja `schema.sql` + ERD (Mermaid `erDiagram`) w README.md + ta sekcja.

## 8. Konwencje

- Nazwy klas `PascalCase`, metody/zmienne `camelCase`, tabele SQL `snake_case` w liczbie mnogiej.
- Jedna klasa = jeden plik, nazwa pliku = nazwa klasy.
- Teksty UI wyłącznie przez mechanizm tłumaczeń (PL/EN), nie na sztywno w widokach/kontrolerach.
- CSS w plikach `.css` (bez stylów inline); motyw przez `data-theme` na `<html>` + `localStorage` + skrypt anty-FOUC w `<head>`.
- JS w modułach (`public/js/*.js`), Fetch API z obsługą statusów, ładowania i błędów.
- Formularze POST z tokenem CSRF; hasła `password_hash`/`password_verify`; `session_regenerate_id(true)` po logowaniu.
- Commity: małe, logiczne, jedna zmiana = jeden commit — format opisany w sekcji „Konwencja commitów” poniżej.
- Bez `var_dump`, `die`, martwego kodu w commitach.

### Konwencja commitów

Format komunikatu:

```text
prefiks: krótki opis w trybie rozkazującym (po angielsku)
```

Przykłady: `feat: add registration form`, `fix: validate empty email`.

| Prefiks    | Zastosowanie      | Przykład                            |
|------------|-------------------|-------------------------------------|
| `feat`     | Nowa funkcja      | `feat: add registration form`       |
| `fix`      | Naprawa błędu     | `fix: validate empty email`         |
| `docs`     | Dokumentacja      | `docs: explain docker setup`        |
| `refactor` | Zmiana struktury  | `refactor: extract user repository` |
| `test`     | Testy             | `test: cover login validation`      |
| `chore`    | Konfiguracja      | `chore: configure nginx`            |

Zasady: opis małą literą, bez kropki na końcu, maks. ~72 znaki; aktualizacja AGENTS.md wchodzi w ten sam commit co zmiana, której dotyczy.

## 9. Znane problemy (do naprawy)

- ~~`docker-compose.yaml` odwołuje się do `docker/nginx/Dockerfile` i `docker/php/Dockerfile`, a pliki nazywają się `dockerfile` (małą literą) — na Linuksie/czystym środowisku build się nie powiedzie.~~ _(naprawione)_
- ~~Wolumen `pg-data` jest zadeklarowany, ale nieużywany przez `db` (dane nie są utrwalane) — dodać `pg-data:/var/lib/postgresql/data`.~~ _(naprawione)_
- ~~`links:` jest przestarzałe — zastąpić `depends_on`.~~ _(naprawione)_
- ~~Brak `.env.example`.~~ _(naprawione)_
- Komentarze w `Routing.php` i `index.php` mają uszkodzone kodowanie (zapisać jako UTF-8).
- ~~Ładowanie klas przez `require_once` — rozważyć autoloader Composer (PSR-4).~~ _(naprawione)_

## 10. Checklista kryteriów oceny

Status: ⬜ brak · 🟨 częściowo · ✅ zrobione

| Nr | Kryterium | Status | Gdzie / uwagi |
|----|-----------|--------|---------------|
| 1 | README.md (opis, uruchomienie, demo, zrzuty, przepływ, architektura, ERD, testy, link) | ⬜ | |
| 2 | Docker (`docker compose up --build`, nazwy usług, wolumen, opis portów/zmiennych) | 🟨 | `docker-compose.yaml` naprawiony; brak opisu w `README.md` |
| 3 | Architektura (warstwy, diagram w README) | 🟨 | są kontrolery i widoki |
| 4 | Routing (metody, statyczna, regex, path param + walidacja, query param, 400/404) | 🟨 | brak query param i 400 |
| 5 | **Obiektowy backend** (warunek konieczny) | 🟨 | PSR-4 autoloader Composer (`App\` -> `src/`), przestrzenie nazw `App\Controller`, `App\Core` |
| 6 | ERD Mermaid w README | ⬜ | |
| 7 | Git (małe commity, merge do main) | 🟨 | |
| 8 | Realizacja tematu (pełny proces, demo) | ⬜ | |
| 9 | HTML semantyczny, wspólne partiale, formularze z etykietami | ⬜ | |
| 10 | PostgreSQL (konfiguracja poza kodem, parametryzacja, mapowanie) | ⬜ | |
| 11 | Złożoność bazy (relacje, ograniczenia, normalizacja) | ⬜ | |
| 12 | Eksport `.sql` | ⬜ | |
| 13 | PHP (nowoczesny, typy, obsługa błędów) | 🟨 | PHP 8.3, `strict_types`, Composer PSR-4 autoloader |
| 14 | JavaScript (walidacja, zdarzenia, DOM, moduły) | ⬜ | |
| 15 | Fetch API (GET + POST, JSON, statusy, loading) | ⬜ | |
| 16 | Design (spójny, CSS w plikach) | ⬜ | |
| 17 | Responsywność (≥5 widoków) | ⬜ | |
| 18 | Dark/light mode (`data-theme`, localStorage, prefers-color-scheme, anty-FOUC) | ⬜ | |
| 19 | WCAG + raport (Lighthouse/axe/WAVE) w README | ⬜ | |
| 20 | Logowanie (hash, bezpieczny komunikat, redirect) | ⬜ | |
| 21 | Sesja (regeneracja ID, flagi cookie) | ⬜ | |
| 22 | Uprawnienia (backend, 401/403) | ⬜ | |
| 23 | Role (≥2, np. USER/ADMIN) | ⬜ | |
| 24 | Wylogowanie (unieważnienie sesji i cookie) | ⬜ | |
| 25 | Widok / trigger / funkcja / transakcja | ⬜ | |
| 26 | JOIN + przemyślane ON DELETE/ON UPDATE | ⬜ | |
| 27 | Security Bingo + tabela w README | ⬜ | |
| 28 | Brak replikacji kodu | 🟨 | |
| 29 | Czystość kodu | 🟨 | |
| 30 | Tłumaczenia PL/EN (także walidacja/błędy) | ⬜ | |
| 31 | Testy jednostkowe (+ polecenie uruchomienia) | ⬜ | zależność `phpunit/phpunit` dodana w `composer.json` |
| 32 | CRON (harmonogram, log, idempotencja) | ⬜ | |
| 33 | PHPMailer / SMTP (Mailpit lokalnie) | ⬜ | usługa mailpit istnieje; zależność `phpmailer/phpmailer` w `composer.json` |
| 34 | Upload zdjęcia (rozmiar, MIME, rozszerzenie, unikalna nazwa, ścieżka w DB) | ⬜ | |
| 35 | `.env` + `.env.example` | ✅ | `.env` w `.gitignore`, utworzono `.env.example` i `src/core/Config.php` |
| 36 | Deploy (publiczny URL w README) | ⬜ | |

## 11. Rejestr zaimplementowanych funkcjonalności

> Dopisuj nowe wiersze na dole. Jeden wiersz = jedna funkcjonalność / zmiana.

| Data | Funkcjonalność | Pliki | Kryteria | Autor/Agent |
|------|----------------|-------|----------|-------------|
| 2026-11-11 | Szkielet Docker (nginx, php-fpm 8.3, postgres 16, mailpit, pgAdmin) | `docker-compose.yaml`, `docker/**` | 2 | — |
| 2026-11-11 | Router obiektowy (GET/POST, parametry `{id}`) + fallback 404 | `Routing.php`, `public/index.php` | 4, 5 | — |
| 2026-11-11 | Kontrolery bazowe i widoki (home, projects, project, not-found) | `src/controllers/*`, `public/views/*` | 3, 5 | — |
| 2026-11-11 | Utworzenie AGENTS.md | `AGENTS.md` | 29 | AI agent |
| 12-11-2026 | Naprawa `docker-compose.yaml` (wielkość liter `Dockerfile`, `depends_on` z `pg_isready`, wolumen `pg-data`) | `docker-compose.yaml`, `docker/nginx/Dockerfile`, `docker/php/Dockerfile` | 2 | Antigravity |
| 12-11-2026 | Dodanie SKILLS.md oraz workspace skills dla agentów (.agents/skills) | `SKILLS.md`, `.agents/skills/**` | 3, 5, 10, 20, 27, 29 | AI agent |
| 12-11-2026 | Utworzenie `.env.example` oraz klasy `Config` (`src/core/Config.php`) i przekazanie `env_file: .env` do `php` | `.env.example`, `src/core/Config.php`, `docker-compose.yaml` | 5, 10, 35 | Antigravity |
| 12-11-2026 | Uproszczenie konwencji commitów (usunięcie prefiksów zadań) i weryfikacja braku zewnętrznych zależności LLM | `AGENTS.md`, `SKILLS.md` | 29 | Antigravity |
| 12-11-2026 | Dodanie Composer z autoloaderem PSR-4 (`App\` -> `src/`), zależnościami (`phpmailer/phpmailer`, `phpunit/phpunit`), przestrzeniami nazw i zastąpienie `require_once` klas autoloaderem | `composer.json`, `docker/php/Dockerfile`, `public/index.php`, `Routing.php`, `src/controllers/*`, `src/core/Config.php` | 5, 13, 31, 33 | Antigravity |
