# Flashcards Leitner App (WDPAI)

Aplikacja webowa do nauki i powtórek fiszek oparta o **system Leitnera** z elementami grywalizacji (monety, klejnoty, serie nauki, sklep z nagrodami i ekwipunek).

---

## 1. Strategia gałęzi i Git Workflow

W projekcie stosowany jest uporządkowany przepływ pracy w oparciu o zadania Jira (projekt `KAN`):

### Gałęzie
- `main` — stabilna gałąź produkcyjna. Kod na `main` **musi być zawsze w stanie budowalnym** i przechodzić wszystkie testy.
- `develop` — gałąź integracyjna dla bieżącego rozwoju aplikacji.
- `feature/KAN-xx-opis` — gałęzie dedykowane dla poszczególnych zadań (np. `feature/KAN-22-git-workflow`, `feature/KAN-38-login`).

### Przepływ pracy (Workflow)
1. Utworzenie gałęzi `feature/KAN-xx-opis` z aktualnej gałęzi bazowej (`develop`).
2. Implementacja funkcjonalności z zachowaniem zasad obiektowości PHP 8.3 oraz separacji warstw.
3. Testowanie i weryfikacja poprawności kodu.
4. Wykonanie atomowych commitów powiązanych z zadaniem.
5. Pull Request (PR) i weryfikacja (code review / CI), a następnie merge do `develop` (i docelowo do `main`).

### Smart Commits i konwencja komunikatów commitów
Każdy commit musi być powiązany z numerem zadania z Jira:
```text
[KAN-xx] prefiks: krótki opis w trybie rozkazującym (po angielsku)
```
Dozwolone prefiksy:
- `feat` — nowa funkcjonalność
- `fix` — naprawa błędu
- `docs` — dokumentacja
- `refactor` — refaktoryzacja kodu (bez zmiany zachowania)
- `test` — testy jednostkowe / integracyjne
- `chore` — konfiguracja, narzędzia, zależności

Przykłady:
- `[KAN-22] docs: define branching strategy and commit convention`
- `[KAN-25] feat: rebuild router with regex params and http methods`
- `[KAN-38] feat: implement login endpoint and credential verification`

---

## 2. Architektura aplikacji i warstwy backendu

Aplikacja oparta jest o obiektową architekturę trójwarstwową (bez zewnętrznych frameworków full-stack). Każdy plik PHP posiada deklarację `declare(strict_types=1);` oraz pełne typowanie parametrów i zwracanych wartości. Zależności przepływają wyłącznie w dół:

```mermaid
flowchart TD
    Client["Przeglądarka / Klient HTTP"] --> Router["Router (App\\Core\\Router)"]
    Router --> Controller["Kontrolery (App\\Controller\\*)"]
    Controller --> View["Widoki / Szablony (public/views/*)"]
    Controller --> Service["Serwisy biznesowe (App\\Service\\*)"]
    Service --> RepoInterface["Interfejsy Repozytoriów (App\\Repository\\*Interface)"]
    RepoInterface --> Repository["Implementacja Repozytoriów (App\\Repository\\*)"]
    Repository --> Database["Baza Danych (App\\Core\\Database - PDO PostgreSQL)"]
    Service --> Model["Modele / Encje (App\\Model\\*)"]
    Repository --> Model
```

### Zasady separacji warstw:
1. **Controller** — przyjmuje żądanie (`Request`), deleguje logikę do serwisów, zwraca obiekt `Response` (HTML/JSON/redirect). Zakaz zapytań SQL i kodu HTML w kontrolerach.
2. **Service** — realizuje logikę biznesową (np. algorytm Leitnera, logowanie, zakupy).
3. **Repository** — jedyna warstwa wykonująca zapytania SQL za pośrednictwem PDO prepared statements. Mapuje wiersze bazy na obiekty `Model`.
4. **Model/Entity** — silnie hermetyzowane obiekty domenowe i enumy.
5. **View** — szablony `*.html.php` z obowiązkowym escapowaniem zmiennych (`e()`).

---

## 3. Model danych i diagram ERD (Mermaid)

Model relacyjny bazy PostgreSQL 16 zaprojektowano w **trzeciej postaci normalnej (3NF)** z silnymi ograniczeniami integralności (klucze obce, unikalność, warunki `CHECK`).

```mermaid
erDiagram
    USERS ||--o| USER_SETTINGS : "posiada"
    USERS ||--o{ DECKS : "tworzy"
    DECKS ||--o{ FLASHCARDS : "zawiera"
    USERS ||--o{ USER_FLASHCARD_PROGRESS : "posiada"
    FLASHCARDS ||--o{ USER_FLASHCARD_PROGRESS : "dotyczy"
    USERS ||--o{ STUDY_SESSIONS : "przeprowadza"
    DECKS ||--o{ STUDY_SESSIONS : "obejmuje"
    STUDY_SESSIONS ||--o{ REVIEW_LOGS : "rejestruje"
    FLASHCARDS ||--o{ REVIEW_LOGS : "ocenia"
    USERS ||--o{ CURRENCY_TRANSACTIONS : "posiada"
    USERS ||--o{ DAILY_ACTIVITY : "rejestruje"
    USERS ||--o{ DAILY_TESTS : "wykonuje"
    USERS ||--o{ USER_ITEMS : "posiada"
    SHOP_ITEMS ||--o{ USER_ITEMS : "kataloguje"
    USERS ||--o{ PASSWORD_RESETS : "posiada"

    USERS {
        int id PK
        string email UK
        string password_hash
        string username
        string role
        int coins
        int gems
        datetime created_at
        datetime updated_at
    }

    USER_SETTINGS {
        int id PK
        int user_id FK,UK
        int daily_goal
        boolean sound_enabled
        string theme
        string timezone
        datetime updated_at
    }

    DECKS {
        int id PK
        int user_id FK
        string title
        string description
        boolean is_public
        int flashcard_count
        datetime created_at
        datetime updated_at
    }

    FLASHCARDS {
        int id PK
        int deck_id FK
        string front
        string back
        string hint
        datetime created_at
        datetime updated_at
    }

    USER_FLASHCARD_PROGRESS {
        int id PK
        int user_id FK
        int flashcard_id FK
        int box
        datetime next_review_at
        int consecutive_correct
        datetime last_reviewed_at
    }

    STUDY_SESSIONS {
        int id PK
        int user_id FK
        int deck_id FK
        datetime started_at
        datetime finished_at
        int total_reviewed
        int correct_count
    }

    REVIEW_LOGS {
        int id PK
        int session_id FK
        int user_id FK
        int flashcard_id FK
        int box_before
        int box_after
        boolean was_correct
        datetime reviewed_at
    }

    CURRENCY_TRANSACTIONS {
        int id PK
        int user_id FK
        string currency
        int amount
        int balance_after
        string transaction_type
        string description
        datetime created_at
    }

    DAILY_ACTIVITY {
        int id PK
        int user_id FK
        date activity_date
        int reviews_count
        boolean goal_achieved
        int streak_days
    }

    DAILY_TESTS {
        int id PK
        int user_id FK
        date test_date
        int score
        int total_questions
        datetime completed_at
    }

    SHOP_ITEMS {
        int id PK
        string name
        string description
        int price_coins
        int price_gems
        string category
        string image_path
        boolean is_active
        datetime created_at
    }

    USER_ITEMS {
        int id PK
        int user_id FK
        int item_id FK
        boolean is_equipped
        datetime acquired_at
    }

    PASSWORD_RESETS {
        int id PK
        int user_id FK
        string token_hash
        datetime expires_at
        datetime used_at
        datetime created_at
    }
```

### Ograniczenia i normalizacja (3NF):
- **Niezależny postęp Leitnera**: `user_flashcard_progress` posiada klucz unikalny `UNIQUE(user_id, flashcard_id)` — wielu użytkowników może uczyć się tej samej talii publicznej bez konfliktów postępu.
- **Audyt walut (monety/klejnoty)**: bieżące saldo w `users.coins` i `users.gems` jest chronione ograniczeniem `CHECK (coins >= 0 AND gems >= 0)`, a każda operacja jest rejestrowana w tabeli `currency_transactions` (księga transakcji z saldem po operacji).
- **Warunki CHECK**: `box BETWEEN 1 AND 5`, `daily_goal > 0`, `price_coins >= 0`, `price_gems >= 0`.
- **Kaskadowe usuwanie (ON DELETE)**: usunięcie talii usuwa jej fiszki i postęp (`CASCADE`), podczas gdy historia zakupów `user_items` zachowuje integralność (`RESTRICT`).

---

## 4. Zaawansowane mechanizmy bazy danych (PostgreSQL 16)

W projekcie wykorzystano zaawansowane mechanizmy silnika PostgreSQL:
1. **Widok statystyk `v_user_stats`**: agreguje w jednym zapytaniu informacje o postępach w pudełkach Leitnera (box 1-5), liczbie talii, ukończonych fiszek oraz passie dni (streak).
2. **Procedury i Triggery `update_updated_at_column`**: automatycznie aktualizują pole `updated_at` w tabelach `users`, `user_settings`, `decks` i `flashcards` przy każdej modyfikacji rekordu.
3. **Trigger denormalizacyjny `update_deck_flashcard_count`**: dynamicznie inkrementuje/dekrementuje liczbę `flashcard_count` w tabeli `decks` po dodaniu lub usunięciu fiszki, eliminując konieczność kosztownych zapytań `COUNT(*)` w listach talii.
4. **Funkcja SQL `leitner_interval(box)`**: funkcja wyliczająca interwał czasowy (`INTERVAL`) dla kolejnych pudełek systemu Leitnera (pudełko 1: 1 dzień, 2: 3 dni, 3: 7 dni, 4: 14 dni, 5: 30 dni).



