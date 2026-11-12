---
name: wdpai-security-auth
description: >-
  Standard uwierzytelniania, autoryzacji ról (RBAC), ochrony CSRF, XSS
  i bezpieczeństwa sesji w projekcie WDPAI.
  Używaj przy implementacji rejestracji, logowania, mechanizmów sesyjnych, formularzy i kontroli dostępu.
---

# Procedura Bezpieczeństwa i Autoryzacji (WDPAI)

## 1. Haszowanie i weryfikacja haseł
- Używaj `password_hash($password, PASSWORD_BCRYPT)` lub `PASSWORD_ARGON2ID`.
- Weryfikacja: `password_verify($password, $hash)`.
- Komunikaty o błędnym logowaniu muszą być generyczne (np. "Nieprawidłowy e-mail lub hasło").

## 2. Zarządzanie sesją
- Po poprawnym uwierzytelnieniu natychmiast regeneruj identyfikator sesji:
  ```php
  session_regenerate_id(true);
  ```
- Konfiguracja ciasteczek sesji w PHP:
  ```php
  session_set_cookie_params([
      'lifetime' => 0,
      'path' => '/',
      'domain' => '',
      'secure' => isset($_SERVER['HTTPS']),
      'httponly' => true,
      'samesite' => 'Lax',
  ]);
  ```
- Wylogowanie unieważnia dane w sesji, usuwa cookie sesyjne oraz wywołuje `session_destroy()`.

## 3. Ochrona CSRF
- Generuj unikalny token CSRF w sesji użytkownika.
- W formularzach dodaj ukryte pole: `<input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">`.
- Przed wykonaniem żądania POST weryfikuj zgodność tokenu.

## 4. Uprawnienia i role (RBAC)
- Obsługuj role użytkowników (np. `enum Role: string { case USER = 'USER'; case ADMIN = 'ADMIN'; }`).
- Blokada nieautoryzowanego dostępu: HTTP 401 (brak zalogowania) lub HTTP 403 (brak uprawnień/roli).
