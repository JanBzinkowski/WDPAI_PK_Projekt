# SKILLS.md — Zestaw umiejętności i procedur dla agentów (WDPAI)

Dokument zawiera spis oraz opis procedur technicznych i standardów realizacji zadań w projekcie (zgodnie ze specyfikacją WDPAI i wytycznymi w [AGENTS.md](file:///c:/Users/janek/Desktop/Studia/WDPAI/my-app/AGENTS.md)).
Poszczególne procedury są również dostępne jako natywne skille workspace w `.agents/skills/`.

---

## 📚 Dostępne procedury i skille

| Skill / Procedura | Lokalizacja | Opis zastosowania | Powiązane kryteria |
|---|---|---|---|
| **wdpai-architecture** | [SKILL.md](file:///.agents/skills/wdpai-architecture/SKILL.md) | Standard czystej architektury obiektowej (Strict types, Router -> Controller -> Service -> Repository -> PDO, brak SQL w kontrolerach). | 3, 5, 13, 28, 29 |
| **wdpai-database** | [SKILL.md](file:///.agents/skills/wdpai-database/SKILL.md) | Procedura modyfikacji bazy danych, schema.sql, transakcje, triggery/widoki, przygotowane zapytania i synchronizacja ERD. | 6, 7, 10, 11, 12, 25, 26 |
| **wdpai-security-auth** | [SKILL.md](file:///.agents/skills/wdpai-security-auth/SKILL.md) | Implementacja uwierzytelniania, ról (RBAC), ochrony CSRF, XSS (`e()`), regeneracji ID sesji, ciasteczek i Security Bingo. | 20, 21, 22, 23, 24, 27 |
| **wdpai-frontend** | [SKILL.md](file:///.agents/skills/wdpai-frontend/SKILL.md) | Standardy Vanilla JS (Fetch API, moduły), semantyczny HTML5, system motywów Dark/Light (localStorage, anty-FOUC), i18n (PL/EN) i WCAG. | 9, 14, 15, 16, 17, 18, 19, 30 |

---

## 1. Architektura Backendowa (`wdpai-architecture`)
- Każdy plik PHP rozpoczyna się od `declare(strict_types=1);`.
- Obowiązkowe typowanie argumentów, właściwości oraz wartości zwracanych.
- Przepływ danych wyłącznie jednokierunkowy:
  `Router -> Controller -> Service -> Repository (Interface) -> Database (PDO)`
- Kontroler deleguje logikę do serwisów i renderuje widok lub zwraca JSON. Zakaz umieszczania zapytań SQL lub znaczników HTML w kontrolerach.
- Zależności wstrzykiwane przez konstruktor (`__construct`).

## 2. Standardy Bazy Danych (`wdpai-database`)
- Wszystkie zapytania SQL parametryzowane przez PDO (`prepare` / `execute`).
- Tabele w liczbie mnogiej (`users`, `flashcards`, `decks`, `leitner_boxes`), nazewnictwo kolumn `snake_case`.
- Zmiany w strukturze bazodanowej trafiają do `database/schema.sql` (i skryptów inicjalizacyjnych w `docker-entrypoint-initdb.d`).
- Wymagane użycie co najmniej jednego zaawansowanego mechanizmu SQL: widok (`VIEW`), wyzwalacz (`TRIGGER`), procedura/funkcja składowana lub transakcja (`BEGIN / COMMIT / ROLLBACK`).
- Aktualizacja diagramu Mermaid `erDiagram` w [README.md](file:///c:/Users/janek/Desktop/Studia/WDPAI/my-app/README.md) przy każdej modyfikacji schematu.

## 3. Bezpieczeństwo i Sesja (`wdpai-security-auth`)
- Hasła hashowane algorytmem `password_hash($password, PASSWORD_BCRYPT)`.
- Przy logowaniu regeneracja ID sesji: `session_regenerate_id(true)`.
- Flagi cookie sesji: `HttpOnly`, `SameSite=Lax`, `Secure` (w środowisku produkcyjnym/HTTPS).
- Formularze `POST` zabezpieczone jednorazowym tokenem CSRF (weryfikacja w serwisie lub middleware).
- Renderowanie zmiennych w szablonach HTML zabezpieczone przed XSS za pomocą funkcji pomocniczej `e($val)` (`htmlspecialchars($val, ENT_QUOTES, 'UTF-8')`).
- Kontrola ról użytkowników (np. `Role::USER`, `Role::ADMIN`) na poziomie backendu z kodami odpowiedzi HTTP `401 Unauthorized` i `403 Forbidden`.

## 4. Frontend, Motywy i Dostępność (`wdpai-frontend`)
- Czysty JavaScript (ES6+ Modules, brak zewnętrznych frameworków bibliotecznych jak React/Vue).
- Komunikacja asynchroniczna z backendem przez `fetch()` z pełną obsługą stanów: oczekiwanie (`loading`), błędy walidacji oraz statusy HTTP.
- Obsługa motywów `dark` / `light` poprzez atrybut `data-theme` w tagu `<html>`, zapis do `localStorage`, z uwzględnieniem `prefers-color-scheme` oraz skryptem zapobiegającym efektowi mignięcia (anty-FOUC) w `<head>`.
- Semantyczne znaczniki HTML5 (`<header>`, `<nav>`, `<main>`, `<article>`, `<section>`, `<footer>`) oraz powiązane etykiety `<label for="...">` z kontrolkami.
- Wielojęzyczność (PL/EN) — brak zahardkodowanych napisów w widokach i komunikatach błędów; użycie słowników tłumaczeń.

---

## 🔄 Procedura Końcowa po Zrealizowaniu Zadania
1. Zweryfikuj poprawność kodu pod kątem `declare(strict_types=1)` i typowania.
2. Zaktualizuj sekcje w [AGENTS.md](file:///c:/Users/janek/Desktop/Studia/WDPAI/my-app/AGENTS.md):
   - Struktura projektu
   - Routing (jeśli dodano trasy)
   - Baza danych (jeśli modyfikowano tabele)
   - Rejestr zaimplementowanych funkcjonalności
   - Checklistę kryteriów
3. Przygotuj commit zgodnie z konwencją: `[NR TASKA] prefiks: opis`.
