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
                                                         ([NR TASKA] prefiks: opis).
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
│       ├── layout.html.php       # główny layout z partialami (header, nav, footer) i anty-FOUC
│       ├── not-found.html.php
│       ├── project.html.php
│       ├── projects.html.php
│       ├── errors/               # widoki błędów HTTP (400, 401, 403, 404, 500, error)
│       └── partials/             # wspólne partiale (header, nav, footer)
└── src/
    ├── controllers/
    │   ├── AppController.php     # App\Controller\AppController: abstrakcyjny kontroler bazowy, render()
    │   ├── DefaultController.php # App\Controller\DefaultController: index, notFound
    │   └── ProjectController.php # App\Controller\ProjectController: index, show(int $id)
    ├── models/                   # App\Model: silnie typowane encje domenowe i enumy
    │   ├── Enums/                # Role, Visibility, Currency, ItemCategory, LeitnerBox
    │   │   ├── Currency.php
    │   │   ├── ItemCategory.php
    │   │   ├── LeitnerBox.php
    │   │   ├── Role.php
    │   │   └── Visibility.php
    │   ├── CurrencyTransaction.php
    │   ├── DailyActivity.php
    │   ├── DailyTest.php
    │   ├── Deck.php
    │   ├── Flashcard.php
    │   ├── FlashcardProgress.php
    │   ├── ModelInterface.php
    │   ├── ShopItem.php
    │   ├── User.php
    │   ├── UserItem.php
    │   └── UserSettings.php
    ├── repositories/             # App\Repository: interfejsy i repozytoria PDO
    │   ├── BaseRepository.php
    │   ├── PgUserStatsRepository.php
    │   ├── RepositoryInterface.php
    │   └── UserStatsRepositoryInterface.php
    └── core/
        ├── Bootstrap.php         # App\Core\Bootstrap: inicjalizacja kontenera, rejestracja tras, dispatch
        ├── Config.php            # App\Core\Config: final readonly Config, typowane gettery z getenv()
        ├── Container.php         # App\Core\Container: kontener DI, bind, singleton, autowiring przez refleksję
        ├── Database.php          # App\Core\Database: połączenie PDO z env, obsługa transakcji (transactional)
        ├── ErrorHandler.php      # App\Core\ErrorHandler: centralna obsługa wyjątków, mapowanie kodów HTTP 400-500, HTML/JSON
        ├── Router.php            # App\Core\Router: rejestracja tras (GET/POST/PUT/PATCH/DELETE), regex params, middleware pipeline, 404/405
        ├── Translator.php        # App\Core\Translator: obsługa tłumaczeń i18n (PL/EN) z podmianą parametrów
        ├── Validator.php         # App\Core\Validator: backendowy walidator reguł (required, email, min, max, between, in, enum, same)
        ├── View.php              # App\Core\View: silnik szablonów *.html.php z obsługą layoutów i partiali
        ├── helpers.php           # globalny helper escapowania e() (htmlspecialchars ENT_QUOTES UTF-8)
        ├── Middleware/           # App\Core\Middleware: MiddlewareInterface
        │   └── MiddlewareInterface.php
        └── Http/                 # App\Core\Http: Request, Response, JsonResponse, RedirectResponse
            ├── JsonResponse.php
            ├── RedirectResponse.php
            ├── Request.php
            └── Response.php
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

- Schemat / eksport: docelowo w `database/schema.sql` (ładowany automatycznie przez `docker-entrypoint-initdb.d`).
- Model danych (3NF):
  * `users` — użytkownicy, hasła (hash), rola (`USER`/`ADMIN`), saldo `coins`, `gems`.
  * `user_settings` — preferencje (cel dzienny, powiadomienia, motyw, strefa).
  * `decks` — talie fiszek (tytuł, opis, flaga publiczna, autor).
  * `flashcards` — pojedyncze fiszki (przód, tył, podpowiedź).
  * `user_flashcard_progress` — postęp Leitnera per użytkownik per fiszka: `box` (1-5), `next_review_at`, `consecutive_correct`.
  * `study_sessions` & `review_logs` — sesje i historia pojedynczych powtórek.
  * `currency_transactions` — audytowalna księga transakcji monet i klejnotów.
  * `daily_activity` & `daily_tests` — serie dni (streaks) i codzienne testy wiedzy.
  * `shop_items` & `user_items` — sklep z nagrodami (awatary, odznaki, motywy) i ekwipunek.
  * `password_resets` — tokeny bezpiecznego resetowania hasła.
- Integralność: `CHECK (box BETWEEN 1 AND 5)`, `CHECK (coins >= 0 AND gems >= 0)`, `CHECK (daily_goal > 0)`.
- Relacje i indeksy: `UNIQUE(user_id, flashcard_id)`, `UNIQUE(user_id, item_id)`, `UNIQUE(user_id, activity_date)`, `UNIQUE(email)`. Indeksy na `next_review_at`, `decks.is_public`, `user_id`.

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
[KAN-xx] prefiks: krótki opis w trybie rozkazującym (po angielsku)
```

Przykłady: `[KAN-22] docs: define branching strategy and commit convention`, `[KAN-38] feat: implement login endpoint and credential verification`.

| Prefiks    | Zastosowanie      | Przykład                                                |
|------------|-------------------|---------------------------------------------------------|
| `feat`     | Nowa funkcja      | `[KAN-37] feat: add registration service and repository`|
| `fix`      | Naprawa błędu     | `[KAN-19] fix: update dockerfile casing`                |
| `docs`     | Dokumentacja      | `[KAN-22] docs: define branching strategy and commit convention` |
| `refactor` | Zmiana struktury  | `[KAN-28] refactor: extract view renderer and helper e` |
| `test`     | Testy             | `[KAN-37] test: cover auth service registration`        |
| `chore`    | Konfiguracja      | `[KAN-21] chore: configure composer dependencies`       |

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
| 3 | Architektura (warstwy, diagram w README) | ✅ | src/Core, Controller, Service, Repository, Model, Exception; diagram Mermaid w README.md |
| 4 | Routing (metody, statyczna, regex, path param + walidacja, query param, 400/404) | ✅ | Router w src/core/Router.php: GET/POST/PUT/PATCH/DELETE, override _method, regex {id:\d+}, 404/405, middleware, testy tests/Unit/RouterTest.php |
| 5 | **Obiektowy backend** (warunek konieczny) | ✅ | PSR-4 autoloader Composer, strict_types, podział na warstwy Controller/Service/Repository/Model/Core |
| 6 | ERD Mermaid w README | ✅ | Diagram ERD Mermaid w README.md (relacje 3NF, klucze, atrybuty) |
| 7 | Git (małe commity, merge do main) | ✅ | workflow gałęzi i konwencja commitów w README.md i AGENTS.md |
| 8 | Realizacja tematu (pełny proces, demo) | ⬜ | |
| 9 | HTML semantyczny, wspólne partiale, formularze z etykietami | ⬜ | |
| 10 | PostgreSQL (konfiguracja poza kodem, parametryzacja, mapowanie) | 🟨 | App\Core\Database (PDO pgsql z env), App\Repository\BaseRepository (prepared statements, transactions) |
| 11 | Złożoność bazy (relacje, ograniczenia, normalizacja) | ✅ | 13 tabel w 3NF, CHECK (box 1-5, saldo>=0, cele>0, cena>=0), UNIQUE, indeksy |
| 12 | Eksport `.sql` | ✅ | database/schema.sql oraz docker/db/01_schema.sql (docker-entrypoint-initdb.d) |
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
| 23 | Role (≥2, np. USER/ADMIN) | ✅ | Enum Role::USER i Role::ADMIN, pole role w users, autoryzacja |
| 24 | Wylogowanie (unieważnienie sesji i cookie) | ⬜ | |
| 25 | Widok / trigger / funkcja / transakcja | ✅ | Widok v_user_stats, triggery updated_at oraz flashcard_count, funkcja leitner_interval |
| 26 | JOIN + przemyślane ON DELETE/ON UPDATE | ✅ | ON DELETE CASCADE dla relacji decks/cards/progress/settings, RESTRICT dla user_items, SET NULL dla logs |
| 27 | Security Bingo + tabela w README | ⬜ | |
| 28 | Brak replikacji kodu | 🟨 | |
| 29 | Czystość kodu | 🟨 | |
| 30 | Tłumaczenia PL/EN (także walidacja/błędy) | 🟨 | App\Core\Translator (PL/EN w lang/pl.php i lang/en.php), komunikaty walidacji w Validator |
| 31 | Testy jednostkowe (+ polecenie uruchomienia) | ⬜ | zależność `phpunit/phpunit` dodana w `composer.json` |
| 32 | CRON (harmonogram, log, idempotencja) | ⬜ | |
| 33 | PHPMailer / SMTP (Mailpit lokalnie) | ⬜ | usługa mailpit istnieje; zależność `phpmailer/phpmailer` w `composer.json` |
| 34 | Upload zdjęcia (rozmiar, MIME, rozszerzenie, unikalna nazwa, ścieżka w DB) | ⬜ | |
| 35 | `.env` + `.env.example` | ✅ | `.env` w `.gitignore`, utworzono `.env.example` i `src/core/Config.php` |
| 36 | Deploy (publiczny URL w README) | ⬜ | |

## 11. Rejestr zaimplementowanych funkcjonalności

> Dopisuj nowe wiersze na dole. Jeden wiersz = jedna funkcjonalność / zmiana.

| Data | Funkcjonalność | Pliki | Kryteria |
|------|----------------|-------|----------|
| 2026-11-11 | Szkielet Docker (nginx, php-fpm 8.3, postgres 16, mailpit, pgAdmin) | `docker-compose.yaml`, `docker/**` | 2 |
| 2026-11-11 | Router obiektowy (GET/POST, parametry `{id}`) + fallback 404 | `Routing.php`, `public/index.php` | 4, 5 |
| 2026-11-11 | Kontrolery bazowe i widoki (home, projects, project, not-found) | `src/controllers/*`, `public/views/*` | 3, 5 |
| 2026-11-11 | Utworzenie AGENTS.md | `AGENTS.md` | 29 |
| 12-11-2026 | Naprawa `docker-compose.yaml` (wielkość liter `Dockerfile`, `depends_on` z `pg_isready`, wolumen `pg-data`) | `docker-compose.yaml`, `docker/nginx/Dockerfile`, `docker/php/Dockerfile` | 2 |
| 12-11-2026 | Dodanie SKILLS.md oraz workspace skills dla agentów (.agents/skills) | `SKILLS.md`, `.agents/skills/**` | 3, 5, 10, 20, 27, 29 |
| 12-11-2026 | Utworzenie `.env.example` oraz klasy `Config` (`src/core/Config.php`) i przekazanie `env_file: .env` do `php` | `.env.example`, `src/core/Config.php`, `docker-compose.yaml` | 5, 10, 35 |
| 12-11-2026 | Uproszczenie konwencji commitów (usunięcie prefiksów zadań) i weryfikacja braku zewnętrznych zależności LLM | `AGENTS.md`, `SKILLS.md` | 29 |
| 12-11-2026 | Dodanie Composer z autoloaderem PSR-4 (`App\` -> `src/`), zależnościami (`phpmailer/phpmailer`, `phpunit/phpunit`), przestrzeniami nazw i zastąpienie `require_once` klas autoloaderem | `composer.json`, `docker/php/Dockerfile`, `public/index.php`, `Routing.php`, `src/controllers/*`, `src/core/Config.php` | 5, 13, 31, 33 |
| 21-11-2026 | Ustalenie strategii gałęzi Git i konwencji commitów ([KAN-xx] prefiks: opis) oraz opis workflow w README (KAN-22) | `README.md`, `AGENTS.md`, `SKILLS.md` | 7 | Antigravity |
| 21-11-2026 | Zaprojektowanie struktury katalogów i warstw backendu OOP (src/Core, Controller, Service, Repository, Model, Exception, public/views/partials) i diagramu warstw w README (KAN-23) | `src/**`, `public/views/partials/**`, `README.md`, `AGENTS.md` | 3, 5 | Antigravity |
| 21-11-2026 | Implementacja obiektów Request i Response (HTML/JSON/redirect), refaktoryzacja kontrolerów i testy jednostkowe Http (KAN-24) | `src/core/Http/**`, `src/controllers/**`, `Routing.php`, `tests/Unit/HttpTest.php`, `AGENTS.md` | 5, 13 | Antigravity |
| 21-11-2026 | Przebudowa Routera (App\Core\Router): metody GET/POST/PUT/PATCH/DELETE, override _method, parametry regex {id:\d+}, query params, obsługa 404/405, pipeline middleware i testy jednostkowe (KAN-25) | `src/core/Router.php`, `src/core/Middleware/**`, `Routing.php`, `public/index.php`, `tests/Unit/RouterTest.php`, `AGENTS.md` | 4 | Antigravity |
| 21-11-2026 | Implementacja kontenera DI (App\Core\Container) z autowiringiem refleksyjnym, klasy Bootstrap i integracja z Routerem (KAN-26) | `src/core/Container.php`, `src/core/Bootstrap.php`, `src/core/Router.php`, `public/index.php`, `tests/Unit/ContainerTest.php`, `AGENTS.md` | 3, 5 | Antigravity |
| 21-11-2026 | Centralna obsługa wyjątków (App\Core\ErrorHandler), klasy wyjątków HTTP (400, 401, 403, 404, 409, 422), widoki błędów i testy (KAN-27) | `src/core/ErrorHandler.php`, `src/exceptions/**`, `public/views/errors/**`, `tests/Unit/ErrorHandlerTest.php`, `AGENTS.md` | 4, 13 | Antigravity |
| 21-11-2026 | Refaktoryzacja AppController, wydzielenie silnika View (layout, partiale), helper e() i currentUser() (KAN-28) | `src/core/View.php`, `src/core/helpers.php`, `src/controllers/AppController.php`, `public/views/layout.html.php`, `public/views/*.html.php`, `tests/Unit/ViewTest.php`, `AGENTS.md` | 5, 9, 28 | Antigravity |
| 21-11-2026 | Implementacja klasy Database (PDO) z transakcjami oraz BaseRepository z helperami fetchOne/fetchAll/execute i testami (KAN-29) | `src/core/Database.php`, `src/repositories/BaseRepository.php`, `src/core/Bootstrap.php`, `tests/Unit/DatabaseTest.php`, `AGENTS.md` | 10, 28 | Antigravity |
| 21-11-2026 | Implementacja backendowego walidatora Validator oraz mechanizmu tłumaczeń i18n Translator (PL/EN) z testami (KAN-30) | `src/core/Validator.php`, `src/core/Translator.php`, `lang/pl.php`, `lang/en.php`, `src/core/Bootstrap.php`, `tests/Unit/ValidatorTest.php`, `AGENTS.md` | 13, 28, 30 | Antigravity |
| 21-11-2026 | Projekt schematu bazy danych w 3NF, opis ograniczeń integralności i dodanie diagramu ERD Mermaid w README (KAN-31) | `README.md`, `AGENTS.md` | 6, 11 | Antigravity |
| 21-11-2026 | Utworzenie fizycznego schematu bazy database/schema.sql i docker/db/01_schema.sql z ograniczeniami CHECK, FK ON DELETE, UNIQUE i indeksami (KAN-32) | `database/schema.sql`, `docker/db/01_schema.sql`, `AGENTS.md` | 11, 12, 26 | Antigravity |
| 22-11-2026 | Utworzenie widoku SQL v_user_stats do agregacji statystyk profilu oraz PgUserStatsRepository z testami (KAN-33) | `database/schema.sql`, `docker/db/01_schema.sql`, `src/repositories/UserStatsRepositoryInterface.php`, `src/repositories/PgUserStatsRepository.php`, `tests/Unit/UserStatsRepositoryTest.php`, `AGENTS.md` | 25, 26 | Antigravity |
| 22-11-2026 | Dodanie triggerów updated_at, licznika fiszek w talii oraz funkcji leitner_interval w PostgreSQL (KAN-34) | `database/schema.sql`, `docker/db/01_schema.sql`, `README.md`, `AGENTS.md` | 25 | Antigravity |
| 22-11-2026 | Utworzenie klas modeli domenowych i enumów (User, Deck, Flashcard, Progress, Shop, Role, LeitnerBox) z testami (KAN-35) | `src/models/**`, `tests/Unit/ModelTest.php`, `AGENTS.md` | 5, 23 | Antigravity |
