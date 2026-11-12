---
name: wdpai-architecture
description: >-
  Standard obiektowej architektury backendu PHP 8.3 dla projektu WDPAI.
  Używaj tego skilla przy tworzeniu lub refaktoryzacji klas: Router, Controller,
  Service, Repository, Entity/Model oraz szablonów widoków.
---

# Procedura Architektury Obiektowej PHP (WDPAI)

## Zasady nadrzędne
1. Każdy plik PHP musi zaczynać się od:
   ```php
   <?php
   declare(strict_types=1);
   ```
2. Wszystkie metody, parametry i pola muszą mieć jednoznacznie określone typy (w tym `void`, `?Type`, `readonly`).
3. Kod proceduralny dopuszczalny jest wyłącznie w punkcie wejścia (`public/index.php`) i skryptach CLI/cron.

## Warstwy i obieg danych
```
Router -> Controller -> Service -> Repository (Interface) -> PDO
              |                         |
          View (.php)              Entity/Model
```

- **Controller**: Dziedziczy po `AppController`. Odpowiada wyłącznie za pobranie żądania, wywołanie serwisu, obsługę błędów i przekazanie danych do `render()` lub JSON. Zero zapytań SQL i zero znaczników HTML.
- **Service**: Zawiera reguły biznesowe, walidację domenową, operacje wieloetapowe.
- **Repository**: Jedyne miejsce wykonujące zapytania SQL. Implementuje interfejs (`*RepositoryInterface`). Zwraca instancje modeli domenowych (`Entity`).
- **Entity**: Obiekty biznesowe z polami prywatnymi, getterami i niemutowalnym identyfikatorem.
- **View**: Szablony `*.html.php` renderowane przez `AppController::render()`. Dane wyjściowe zawsze zabezpieczone `htmlspecialchars` (helper `e()`).
