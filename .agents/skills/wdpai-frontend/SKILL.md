---
name: wdpai-frontend
description: >-
  Standard frontendu (Vanilla JS ES6, CSS, Dark/Light mode, i18n PL/EN, WCAG) w projekcie WDPAI.
  Używaj tego skilla przy tworzeniu skryptów JS, stylów CSS, motywów, integracji Fetch API oraz szablonów HTML.
---

# Procedura Frontendu i Dostępności (WDPAI)

## 1. Architektura JavaScript
- Czysty JavaScript (Vanilla JS) zorganizowany w moduły ES6 (`public/js/*.js`).
- Zakaz bibliotek typu jQuery, React itp.
- Zapytania asynchroniczne przez `fetch()` z obsługą:
  - Wskaźnika ładowania (spinner / disabled button),
  - Statusów odpowiedzi HTTP (`response.ok`, 4xx, 5xx),
  - Prezentacji błędów użytkownikowi.

## 2. Style CSS i Motyw Dark/Light
- Style w plikach `.css` (brak stylów inline).
- Obsługa motywów przez atrybut `data-theme="dark"` / `data-theme="light"` na tagu `<html>`.
- Zapis wybranego motywu do `localStorage`.
- Skrypt anty-FOUC w tagu `<head>`:
  ```html
  <script>
    const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    document.documentElement.setAttribute('data-theme', savedTheme);
  </script>
  ```

## 3. Semantyczny HTML i WCAG
- Wykorzystuj semantyczne znaczniki (`<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<footer>`).
- Każde pole formularza musi mieć powiązaną etykietę `<label for="field-id">`.
- Kontrast elementów spełniający wytyczne WCAG AA.
- Tłumaczenia: wszystkie teksty UI pobierane ze słowników (PL/EN), zakaz wpisywania stałych napisów w widokach na sztywno.
