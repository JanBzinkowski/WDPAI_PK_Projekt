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
