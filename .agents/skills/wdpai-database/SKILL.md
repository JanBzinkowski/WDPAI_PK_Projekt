---
name: wdpai-database
description: >-
  Standard pracy z bazą PostgreSQL 16 w projekcie WDPAI.
  Używaj tego skilla przy projektowaniu tabel, modyfikacjach schema.sql,
  pisaniu zapytań PDO, tworzeniu triggerów, widoków, transakcji oraz aktualizacji ERD.
---

# Procedura Bazy Danych PostgreSQL (WDPAI)

## 1. Wytyczne SQL i projektowania schematu
- Nazwy tabel w liczbie mnogiej (`users`, `flashcards`, `decks`, `leitner_boxes`).
- Nazwy kolumn w formacie `snake_case` (np. `user_id`, `created_at`).
- Każda relacja powinna mieć zdefiniowane klucze obce z `ON DELETE` / `ON UPDATE` (np. `CASCADE` lub `RESTRICT`).
- Wszystkie zapytania PDO muszą korzystać z mechanizmu prepared statements:
  ```php
  $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
  $stmt->execute(['email' => $email]);
  ```

## 2. Zaawansowane mechanizmy SQL (Wymóg projektu)
Każda baza powinna zawierać przynajmniej:
- Widok (`CREATE VIEW v_user_deck_stats AS ...`) do agregacji postępów nauki.
- Wyzwalacz lub funkcję (`CREATE TRIGGER ...`) np. aktualizującą czas modyfikacji lub pozycję w systemie Leitnera.
- Transakcje PDO (`beginTransaction`, `commit`, `rollBack`) przy operacjach złożonych.

## 3. Synchronizacja
Po dodaniu lub zmianie tabel:
1. Zaktualizuj plik `database/schema.sql`.
2. Zaktualizuj diagram Mermaid `erDiagram` w `README.md`.
3. Zaktualizuj sekcję 7 w `AGENTS.md`.
