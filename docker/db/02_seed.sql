-- =============================================================================
-- Flashcards Leitner App - Seed Data (docker-entrypoint-initdb.d)
-- =============================================================================
INSERT INTO users (id, email, password_hash, username, role, coins, gems, created_at, updated_at) VALUES
(1, 'user@demo.pl', '$2y$10$jGcGHObSjY0lnocSIA3OieQl7Www5mUgEAxe3zvPOQYB5p/P5u9G2', 'Jan Kowalski', 'USER', 150, 25, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 'admin@demo.pl', '$2y$10$XTgByVnwt2VJeTXhJqnAF.zplfAXZSkyhBRquPrTIKMacYVcP37ry', 'Administrator', 'ADMIN', 999, 100, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO NOTHING;

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));

INSERT INTO user_settings (user_id, daily_goal, sound_enabled, theme, timezone, created_at, updated_at) VALUES
(1, 15, TRUE, 'dark', 'Europe/Warsaw', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 25, TRUE, 'dark', 'Europe/Warsaw', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (user_id) DO NOTHING;

INSERT INTO decks (id, user_id, title, description, is_public, flashcard_count, created_at, updated_at) VALUES
(1, 1, 'Angielski B2 - Słownictwo biznesowe', 'Najważniejsze zwroty i idiomy przydatne w środowisku korporacyjnym i negocjacjach.', TRUE, 5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 2, 'Podstawy PHP 8.3 i OOP', 'Kluczowe pojęcia programowania obiektowego w PHP: typowanie, hermetyzacja, interfejsy.', TRUE, 5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 1, 'Historia Polski - Daty i wydarzenia', 'Prywatny zestaw najważniejszych dat z historii nowożytnej.', FALSE, 3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO NOTHING;

SELECT setval('decks_id_seq', (SELECT MAX(id) FROM decks));

INSERT INTO flashcards (id, deck_id, front, back, hint, created_at, updated_at) VALUES
(1, 1, 'To think outside the box', 'Myśleć nieszablonowo, kreatywnie', 'Idiom biznesowy związany z innowacją', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 1, 'Touch base with someone', 'Skontaktować się z kimś na krótko', 'Często używane w mailach projektowych', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 1, 'Bottom line', 'Kluczowa kwestia, wynik finansowy netto', 'Ostatnia linia bilansu', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(4, 1, 'Win-win situation', 'Sytuacja korzystna dla obu stron', 'Strategia negocjacji', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(5, 1, 'Core competency', 'Kluczowa kompetencja organizacji', 'Wyróżnik rynkowy przedsiębiorstwa', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),

(6, 2, 'Czym jest enkapsulacja (hermetyzacja)?', 'Ukrywaniem wewnętrznego stanu obiektu i udostępnianiem go tylko przez metody publiczne (gettery/settery)', 'Jeden z 4 filarów OOP', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(7, 2, 'Do czego służy declare(strict_types=1)?', 'Wymusza ścisłe sprawdzanie typów argumentów i zwracanych wartości w pliku PHP', 'Umieszczane na samym początku pliku', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(8, 2, 'Czym różni się interfejs od klasy abstrakcyjnej?', 'Interfejs definiuje wyłącznie kontrakt bez implementacji metod (przed PHP 8), klasa abstrakcyjna może mieć stan i kod', 'Wzorzec projektowy', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(9, 2, 'Co oznacza słowo kluczowe readonly w PHP 8.1+?', 'Właściwość może być zainicjalizowana tylko raz i nie może być zmieniona', 'Niemutowalność obiektu', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(10, 2, 'Dlaczego należy stosować PDO Prepared Statements?', 'Chroni przed atakami typu SQL Injection rozdzielając kod zapytania od danych', 'Bezpieczeństwo bazy danych', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),

(11, 3, '1025 rok', 'Koronacja Bolesława Chrobrego na pierwszego króla Polski', 'Gniezno', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(12, 3, '1410 rok', 'Bitwa pod Grunwaldem', 'Władysław II Jagiełło', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(13, 3, '1791 rok (3 maja)', 'Uchwalenie Konstytucji 3 Maja', 'Pierwsza w Europie, druga na świecie', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO NOTHING;

SELECT setval('flashcards_id_seq', (SELECT MAX(id) FROM flashcards));

INSERT INTO user_flashcard_progress (user_id, flashcard_id, box, next_review_at, consecutive_correct, last_reviewed_at) VALUES
(1, 1, 1, CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP - INTERVAL '1 day'),
(1, 2, 2, CURRENT_TIMESTAMP + INTERVAL '2 days', 1, CURRENT_TIMESTAMP - INTERVAL '1 day'),
(1, 3, 3, CURRENT_TIMESTAMP + INTERVAL '6 days', 2, CURRENT_TIMESTAMP - INTERVAL '1 day'),
(1, 4, 4, CURRENT_TIMESTAMP + INTERVAL '12 days', 3, CURRENT_TIMESTAMP - INTERVAL '2 days'),
(1, 5, 5, CURRENT_TIMESTAMP + INTERVAL '28 days', 5, CURRENT_TIMESTAMP - INTERVAL '2 days'),
(1, 6, 1, CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP)
ON CONFLICT (user_id, flashcard_id) DO NOTHING;

INSERT INTO daily_activity (user_id, activity_date, reviews_count, goal_achieved, streak_days) VALUES
(1, CURRENT_DATE - INTERVAL '2 days', 18, TRUE, 3),
(1, CURRENT_DATE - INTERVAL '1 day', 22, TRUE, 4),
(1, CURRENT_DATE, 15, TRUE, 5)
ON CONFLICT (user_id, activity_date) DO NOTHING;

INSERT INTO daily_tests (user_id, test_date, score, total_questions, completed_at) VALUES
(1, CURRENT_DATE - INTERVAL '1 day', 9, 10, CURRENT_TIMESTAMP - INTERVAL '1 day')
ON CONFLICT (user_id, test_date) DO NOTHING;

INSERT INTO shop_items (id, name, description, price_coins, price_gems, category, image_path, is_active, created_at) VALUES
(1, 'Awatary: Mądra Sowa', 'Elegancki awatar symbolizujący mądrość i systematyczność.', 100, 0, 'AVATAR', '/images/avatars/owl.svg', TRUE, CURRENT_TIMESTAMP),
(2, 'Awatary: Złoty Rycerz', 'Prestiżowy awatar dla mistrzów powtórek.', 250, 10, 'AVATAR', '/images/avatars/knight.svg', TRUE, CURRENT_TIMESTAMP),
(3, 'Odznaka: Mistrz Pamięci', 'Ekskluzywna odznaka do profilu za ukończenie 100 powtórek.', 50, 0, 'BADGE', '/images/badges/memory.svg', TRUE, CURRENT_TIMESTAMP),
(4, 'Motyw: Cyberpunk Neon', 'Wyrazisty, neonowy schemat kolorystyczny aplikacji.', 300, 15, 'THEME', NULL, TRUE, CURRENT_TIMESTAMP),
(5, 'Booster: Podwójne Monety (24h)', 'Podwaja zdobywane monety za każdą poprawną powtórkę przez 24 godziny.', 50, 5, 'BOOSTER', NULL, TRUE, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO NOTHING;

SELECT setval('shop_items_id_seq', (SELECT MAX(id) FROM shop_items));

INSERT INTO user_items (user_id, item_id, is_equipped, acquired_at) VALUES
(1, 1, TRUE, CURRENT_TIMESTAMP - INTERVAL '3 days')
ON CONFLICT (user_id, item_id) DO NOTHING;

INSERT INTO currency_transactions (user_id, currency, amount, balance_after, transaction_type, description, created_at) VALUES
(1, 'COINS', 50, 50, 'WELCOME_BONUS', 'Bonus powitalny za rejestrację', CURRENT_TIMESTAMP - INTERVAL '5 days'),
(1, 'COINS', 200, 250, 'STREAK_REWARD', 'Nagroda za 3-dniową serię nauki', CURRENT_TIMESTAMP - INTERVAL '3 days'),
(1, 'COINS', -100, 150, 'SHOP_PURCHASE', 'Zakup przedmiotu: Awatary: Mądra Sowa', CURRENT_TIMESTAMP - INTERVAL '3 days'),
(1, 'GEMS', 25, 25, 'ACHIEVEMENT', 'Nagroda za pierwszy rozwiązany test dzienny', CURRENT_TIMESTAMP - INTERVAL '1 day');
