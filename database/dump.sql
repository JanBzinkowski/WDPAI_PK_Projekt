-- =============================================================================
-- Flashcards Leitner App - Physical Database Schema (PostgreSQL 16)
-- Zgodne z zadaniem KAN-32, model 3NF, klucze, ograniczenia CHECK i indeksy
-- =============================================================================

-- Upewnienie siÄ™ o czystoĹ›ci schematu przy inicjalizacji
DROP TABLE IF EXISTS password_resets CASCADE;
DROP TABLE IF EXISTS user_items CASCADE;
DROP TABLE IF EXISTS shop_items CASCADE;
DROP TABLE IF EXISTS daily_tests CASCADE;
DROP TABLE IF EXISTS daily_activity CASCADE;
DROP TABLE IF EXISTS currency_transactions CASCADE;
DROP TABLE IF EXISTS review_logs CASCADE;
DROP TABLE IF EXISTS study_sessions CASCADE;
DROP TABLE IF EXISTS user_flashcard_progress CASCADE;
DROP TABLE IF EXISTS flashcards CASCADE;
DROP TABLE IF EXISTS decks CASCADE;
DROP TABLE IF EXISTS user_settings CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- 1. Tabela uĹĽytkownikĂłw (users)
-- Przechowuje konta, hasĹ‚a, rolÄ™ RBAC oraz aktualne saldo walut
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'USER' CHECK (role IN ('USER', 'ADMIN')),
    coins INT NOT NULL DEFAULT 0 CHECK (coins >= 0),
    gems INT NOT NULL DEFAULT 0 CHECK (gems >= 0),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabela ustawieĹ„ uĹĽytkownika (user_settings)
-- ON DELETE CASCADE: usuniÄ™cie profilu uĹĽytkownika usuwa jego ustawienia
CREATE TABLE user_settings (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    daily_goal INT NOT NULL DEFAULT 10 CHECK (daily_goal > 0),
    sound_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    theme VARCHAR(20) NOT NULL DEFAULT 'dark' CHECK (theme IN ('light', 'dark', 'system')),
    timezone VARCHAR(50) NOT NULL DEFAULT 'Europe/Warsaw',
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabela talii fiszek (decks)
-- ON DELETE CASCADE: usuniÄ™cie konta autora usuwa stworzone przez niego talie
CREATE TABLE decks (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    flashcard_count INT NOT NULL DEFAULT 0 CHECK (flashcard_count >= 0),
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabela pojedynczych fiszek (flashcards)
-- ON DELETE CASCADE: usuniÄ™cie talii automatycznie usuwa wszystkie jej fiszki
CREATE TABLE flashcards (
    id SERIAL PRIMARY KEY,
    deck_id INT NOT NULL REFERENCES decks(id) ON DELETE CASCADE,
    front TEXT NOT NULL,
    back TEXT NOT NULL,
    hint TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabela indywidualnego postÄ™pu Leitnera (user_flashcard_progress)
-- KaĹĽdy uĹĽytkownik uczy siÄ™ niezaleĹĽnie (UNIQUE user_id + flashcard_id)
-- PudeĹ‚ka Leitnera od 1 do 5 (CHECK box BETWEEN 1 AND 5)
-- ON DELETE CASCADE: usuniÄ™cie fiszki lub uĹĽytkownika czyĹ›ci powiÄ…zany postÄ™p
CREATE TABLE user_flashcard_progress (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    flashcard_id INT NOT NULL REFERENCES flashcards(id) ON DELETE CASCADE,
    box INT NOT NULL DEFAULT 1 CHECK (box BETWEEN 1 AND 5),
    next_review_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    consecutive_correct INT NOT NULL DEFAULT 0 CHECK (consecutive_correct >= 0),
    last_reviewed_at TIMESTAMP WITH TIME ZONE NULL,
    CONSTRAINT uq_user_flashcard UNIQUE (user_id, flashcard_id)
);

-- 6. Tabela sesji nauki (study_sessions)
-- ON DELETE CASCADE: powiÄ…zana z uĹĽytkownikiem i taliÄ…
CREATE TABLE study_sessions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    deck_id INT NOT NULL REFERENCES decks(id) ON DELETE CASCADE,
    started_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP WITH TIME ZONE NULL,
    total_reviewed INT NOT NULL DEFAULT 0 CHECK (total_reviewed >= 0),
    correct_count INT NOT NULL DEFAULT 0 CHECK (correct_count >= 0)
);

-- 7. Tabela audytu powtĂłrek (review_logs)
-- ON DELETE SET NULL: usuniÄ™cie sesji lub fiszki zachowuje statystyki powtĂłrek uĹĽytkownika
CREATE TABLE review_logs (
    id SERIAL PRIMARY KEY,
    session_id INT NULL REFERENCES study_sessions(id) ON DELETE SET NULL,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    flashcard_id INT NULL REFERENCES flashcards(id) ON DELETE SET NULL,
    box_before INT NOT NULL CHECK (box_before BETWEEN 1 AND 5),
    box_after INT NOT NULL CHECK (box_after BETWEEN 1 AND 5),
    was_correct BOOLEAN NOT NULL,
    reviewed_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 8. Tabela ksiÄ™gi walut (currency_transactions)
-- Audyt kaĹĽdej zmiany stanu monet i klejnotĂłw uĹĽytkownika
CREATE TABLE currency_transactions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    currency VARCHAR(10) NOT NULL CHECK (currency IN ('COINS', 'GEMS')),
    amount INT NOT NULL,
    balance_after INT NOT NULL CHECK (balance_after >= 0),
    transaction_type VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 9. Tabela codziennej aktywnoĹ›ci i serii (daily_activity)
-- Zabezpiecza unikalnoĹ›Ä‡ wpisu na dany dzieĹ„ per uĹĽytkownik
CREATE TABLE daily_activity (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    activity_date DATE NOT NULL DEFAULT CURRENT_DATE,
    reviews_count INT NOT NULL DEFAULT 0 CHECK (reviews_count >= 0),
    goal_achieved BOOLEAN NOT NULL DEFAULT FALSE,
    streak_days INT NOT NULL DEFAULT 0 CHECK (streak_days >= 0),
    CONSTRAINT uq_user_activity_date UNIQUE (user_id, activity_date)
);

-- 10. Tabela codziennych testĂłw wiedzy (daily_tests)
CREATE TABLE daily_tests (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    test_date DATE NOT NULL DEFAULT CURRENT_DATE,
    score INT NOT NULL DEFAULT 0 CHECK (score >= 0),
    total_questions INT NOT NULL DEFAULT 10 CHECK (total_questions > 0),
    completed_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_user_test_date UNIQUE (user_id, test_date)
);

-- 11. Tabela przedmiotĂłw w sklepie (shop_items)
CREATE TABLE shop_items (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    price_coins INT NOT NULL DEFAULT 0 CHECK (price_coins >= 0),
    price_gems INT NOT NULL DEFAULT 0 CHECK (price_gems >= 0),
    category VARCHAR(30) NOT NULL CHECK (category IN ('AVATAR', 'BADGE', 'THEME', 'BOOSTER')),
    image_path VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 12. Ekwipunek uĹĽytkownika (user_items)
-- ON DELETE RESTRICT: uniemoĹĽliwia skasowanie przedmiotu sklepu, jeĹ›li zostaĹ‚ juĹĽ zakupiony
CREATE TABLE user_items (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    item_id INT NOT NULL REFERENCES shop_items(id) ON DELETE RESTRICT,
    is_equipped BOOLEAN NOT NULL DEFAULT FALSE,
    acquired_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_user_item UNIQUE (user_id, item_id)
);

-- 13. Tokeny resetu hasĹ‚a (password_resets)
CREATE TABLE password_resets (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    used_at TIMESTAMP WITH TIME ZONE NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =============================================================================
-- Indeksy wydajnoĹ›ciowe
-- =============================================================================
CREATE INDEX idx_user_flashcard_progress_next_review ON user_flashcard_progress(next_review_at);
CREATE INDEX idx_user_flashcard_progress_user ON user_flashcard_progress(user_id);
CREATE INDEX idx_decks_is_public ON decks(is_public);
CREATE INDEX idx_decks_user_id ON decks(user_id);
CREATE INDEX idx_flashcards_deck_id ON flashcards(deck_id);
CREATE INDEX idx_review_logs_user_reviewed ON review_logs(user_id, reviewed_at);
CREATE INDEX idx_currency_transactions_user ON currency_transactions(user_id);

-- =============================================================================
-- Widok statystyk uĹĽytkownika (v_user_stats) - Zadanie KAN-33
-- Uzasadniona agregacja danych profilu w jednym zapytaniu SQL
-- =============================================================================
CREATE OR REPLACE VIEW v_user_stats AS
SELECT 
    u.id AS user_id,
    u.username,
    u.email,
    u.role,
    u.coins,
    u.gems,
    COALESCE(COUNT(DISTINCT d.id), 0) AS total_decks,
    COALESCE(COUNT(DISTINCT p.id), 0) AS total_cards_learned,
    COALESCE(COUNT(DISTINCT CASE WHEN p.box = 1 THEN p.id END), 0) AS box_1_count,
    COALESCE(COUNT(DISTINCT CASE WHEN p.box = 2 THEN p.id END), 0) AS box_2_count,
    COALESCE(COUNT(DISTINCT CASE WHEN p.box = 3 THEN p.id END), 0) AS box_3_count,
    COALESCE(COUNT(DISTINCT CASE WHEN p.box = 4 THEN p.id END), 0) AS box_4_count,
    COALESCE(COUNT(DISTINCT CASE WHEN p.box = 5 THEN p.id END), 0) AS box_5_count,
    COALESCE(MAX(a.streak_days), 0) AS current_streak,
    COALESCE(SUM(a.reviews_count), 0) AS total_reviews_done
FROM users u
LEFT JOIN decks d ON d.user_id = u.id
LEFT JOIN user_flashcard_progress p ON p.user_id = u.id
LEFT JOIN daily_activity a ON a.user_id = u.id
GROUP BY u.id, u.username, u.email, u.role, u.coins, u.gems;

-- =============================================================================
-- Triggery i funkcje PostgreSQL (Zadanie KAN-34)
-- 1. Automatyczna aktualizacja kolumny updated_at
-- 2. Funkcja interwaĹ‚Ăłw Leitnera leitner_interval(box)
-- 3. Automatyczna aktualizacja licznika fiszek w talii
-- =============================================================================

CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_users_updated_at
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_user_settings_updated_at
BEFORE UPDATE ON user_settings
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_decks_updated_at
BEFORE UPDATE ON decks
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER trg_flashcards_updated_at
BEFORE UPDATE ON flashcards
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Funkcja pomocnicza wyliczajÄ…ca interwaĹ‚ czasowy dla pudeĹ‚ka Leitnera
CREATE OR REPLACE FUNCTION leitner_interval(p_box INT)
RETURNS INTERVAL AS $$
BEGIN
    RETURN CASE p_box
        WHEN 1 THEN INTERVAL '1 day'
        WHEN 2 THEN INTERVAL '3 days'
        WHEN 3 THEN INTERVAL '7 days'
        WHEN 4 THEN INTERVAL '14 days'
        WHEN 5 THEN INTERVAL '30 days'
        ELSE INTERVAL '1 day'
    END;
END;
$$ LANGUAGE plpgsql IMMUTABLE;

-- Trigger dbajÄ…cy o spĂłjnoĹ›Ä‡ licznika fiszek w tabeli decks
CREATE OR REPLACE FUNCTION update_deck_flashcard_count()
RETURNS TRIGGER AS $$
BEGIN
    IF (TG_OP = 'INSERT') THEN
        UPDATE decks SET flashcard_count = flashcard_count + 1 WHERE id = NEW.deck_id;
    ELSIF (TG_OP = 'DELETE') THEN
        UPDATE decks SET flashcard_count = GREATEST(0, flashcard_count - 1) WHERE id = OLD.deck_id;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_flashcards_count
AFTER INSERT OR DELETE ON flashcards
FOR EACH ROW
EXECUTE FUNCTION update_deck_flashcard_count();


-- =============================================================================
-- Flashcards Leitner App - Seed Data (Dane demonstracyjne)
-- Konta demo, talie fiszek zgodne z Figma, sklep, postÄ™p Leitnera i transakcje
-- =============================================================================

-- Konta demo
-- HasĹ‚o dla user@demo.pl: Demo1234!
-- HasĹ‚o dla admin@demo.pl: Admin1234!
INSERT INTO users (id, email, password_hash, username, role, coins, gems, created_at, updated_at) VALUES
(1, 'user@demo.pl', '$2y$10$jGcGHObSjY0lnocSIA3OieQl7Www5mUgEAxe3zvPOQYB5p/P5u9G2', 'Jan Kowalski', 'USER', 150, 25, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 'admin@demo.pl', '$2y$10$XTgByVnwt2VJeTXhJqnAF.zplfAXZSkyhBRquPrTIKMacYVcP37ry', 'Administrator', 'ADMIN', 999, 100, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO NOTHING;

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));

-- Ustawienia uĹĽytkownikĂłw
INSERT INTO user_settings (user_id, daily_goal, sound_enabled, theme, timezone, created_at, updated_at) VALUES
(1, 15, TRUE, 'dark', 'Europe/Warsaw', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 25, TRUE, 'dark', 'Europe/Warsaw', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (user_id) DO NOTHING;

-- Talie fiszek (Decks)
INSERT INTO decks (id, user_id, title, description, is_public, flashcard_count, created_at, updated_at) VALUES
(1, 1, 'Angielski B2 - SĹ‚ownictwo biznesowe', 'NajwaĹĽniejsze zwroty i idiomy przydatne w Ĺ›rodowisku korporacyjnym i negocjacjach.', TRUE, 5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 2, 'Podstawy PHP 8.3 i OOP', 'Kluczowe pojÄ™cia programowania obiektowego w PHP: typowanie, hermetyzacja, interfejsy.', TRUE, 5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 1, 'Historia Polski - Daty i wydarzenia', 'Prywatny zestaw najwaĹĽniejszych dat z historii nowoĹĽytnej.', FALSE, 3, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO NOTHING;

SELECT setval('decks_id_seq', (SELECT MAX(id) FROM decks));

-- Fiszki (Flashcards)
INSERT INTO flashcards (id, deck_id, front, back, hint, created_at, updated_at) VALUES
(1, 1, 'To think outside the box', 'MyĹ›leÄ‡ nieszablonowo, kreatywnie', 'Idiom biznesowy zwiÄ…zany z innowacjÄ…', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(2, 1, 'Touch base with someone', 'SkontaktowaÄ‡ siÄ™ z kimĹ› na krĂłtko', 'CzÄ™sto uĹĽywane w mailach projektowych', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(3, 1, 'Bottom line', 'Kluczowa kwestia, wynik finansowy netto', 'Ostatnia linia bilansu', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(4, 1, 'Win-win situation', 'Sytuacja korzystna dla obu stron', 'Strategia negocjacji', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(5, 1, 'Core competency', 'Kluczowa kompetencja organizacji', 'WyrĂłĹĽnik rynkowy przedsiÄ™biorstwa', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),

(6, 2, 'Czym jest enkapsulacja (hermetyzacja)?', 'Ukrywaniem wewnÄ™trznego stanu obiektu i udostÄ™pnianiem go tylko przez metody publiczne (gettery/settery)', 'Jeden z 4 filarĂłw OOP', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(7, 2, 'Do czego sĹ‚uĹĽy declare(strict_types=1)?', 'Wymusza Ĺ›cisĹ‚e sprawdzanie typĂłw argumentĂłw i zwracanych wartoĹ›ci w pliku PHP', 'Umieszczane na samym poczÄ…tku pliku', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(8, 2, 'Czym rĂłĹĽni siÄ™ interfejs od klasy abstrakcyjnej?', 'Interfejs definiuje wyĹ‚Ä…cznie kontrakt bez implementacji metod (przed PHP 8), klasa abstrakcyjna moĹĽe mieÄ‡ stan i kod', 'Wzorzec projektowy', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(9, 2, 'Co oznacza sĹ‚owo kluczowe readonly w PHP 8.1+?', 'WĹ‚aĹ›ciwoĹ›Ä‡ moĹĽe byÄ‡ zainicjalizowana tylko raz i nie moĹĽe byÄ‡ zmieniona', 'NiemutowalnoĹ›Ä‡ obiektu', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(10, 2, 'Dlaczego naleĹĽy stosowaÄ‡ PDO Prepared Statements?', 'Chroni przed atakami typu SQL Injection rozdzielajÄ…c kod zapytania od danych', 'BezpieczeĹ„stwo bazy danych', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),

(11, 3, '1025 rok', 'Koronacja BolesĹ‚awa Chrobrego na pierwszego krĂłla Polski', 'Gniezno', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(12, 3, '1410 rok', 'Bitwa pod Grunwaldem', 'WĹ‚adysĹ‚aw II JagieĹ‚Ĺ‚o', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
(13, 3, '1791 rok (3 maja)', 'Uchwalenie Konstytucji 3 Maja', 'Pierwsza w Europie, druga na Ĺ›wiecie', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO NOTHING;

SELECT setval('flashcards_id_seq', (SELECT MAX(id) FROM flashcards));

-- PostÄ™p Leitnera dla user 1 (rozĹ‚oĹĽenie po pudeĹ‚kach 1-5)
INSERT INTO user_flashcard_progress (user_id, flashcard_id, box, next_review_at, consecutive_correct, last_reviewed_at) VALUES
(1, 1, 1, CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP - INTERVAL '1 day'),
(1, 2, 2, CURRENT_TIMESTAMP + INTERVAL '2 days', 1, CURRENT_TIMESTAMP - INTERVAL '1 day'),
(1, 3, 3, CURRENT_TIMESTAMP + INTERVAL '6 days', 2, CURRENT_TIMESTAMP - INTERVAL '1 day'),
(1, 4, 4, CURRENT_TIMESTAMP + INTERVAL '12 days', 3, CURRENT_TIMESTAMP - INTERVAL '2 days'),
(1, 5, 5, CURRENT_TIMESTAMP + INTERVAL '28 days', 5, CURRENT_TIMESTAMP - INTERVAL '2 days'),
(1, 6, 1, CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP)
ON CONFLICT (user_id, flashcard_id) DO NOTHING;

-- AktywnoĹ›Ä‡ codzienna i serie (Daily Activity)
INSERT INTO daily_activity (user_id, activity_date, reviews_count, goal_achieved, streak_days) VALUES
(1, CURRENT_DATE - INTERVAL '2 days', 18, TRUE, 3),
(1, CURRENT_DATE - INTERVAL '1 day', 22, TRUE, 4),
(1, CURRENT_DATE, 15, TRUE, 5)
ON CONFLICT (user_id, activity_date) DO NOTHING;

-- Codzienne testy (Daily Tests)
INSERT INTO daily_tests (user_id, test_date, score, total_questions, completed_at) VALUES
(1, CURRENT_DATE - INTERVAL '1 day', 9, 10, CURRENT_TIMESTAMP - INTERVAL '1 day')
ON CONFLICT (user_id, test_date) DO NOTHING;

-- Przedmioty w sklepie (Shop Items)
INSERT INTO shop_items (id, name, description, price_coins, price_gems, category, image_path, is_active, created_at) VALUES
(1, 'Awatary: MÄ…dra Sowa', 'Elegancki awatar symbolizujÄ…cy mÄ…droĹ›Ä‡ i systematycznoĹ›Ä‡.', 100, 0, 'AVATAR', '/images/avatars/owl.svg', TRUE, CURRENT_TIMESTAMP),
(2, 'Awatary: ZĹ‚oty Rycerz', 'PrestiĹĽowy awatar dla mistrzĂłw powtĂłrek.', 250, 10, 'AVATAR', '/images/avatars/knight.svg', TRUE, CURRENT_TIMESTAMP),
(3, 'Odznaka: Mistrz PamiÄ™ci', 'Ekskluzywna odznaka do profilu za ukoĹ„czenie 100 powtĂłrek.', 50, 0, 'BADGE', '/images/badges/memory.svg', TRUE, CURRENT_TIMESTAMP),
(4, 'Motyw: Cyberpunk Neon', 'Wyrazisty, neonowy schemat kolorystyczny aplikacji.', 300, 15, 'THEME', NULL, TRUE, CURRENT_TIMESTAMP),
(5, 'Booster: PodwĂłjne Monety (24h)', 'Podwaja zdobywane monety za kaĹĽdÄ… poprawnÄ… powtĂłrkÄ™ przez 24 godziny.', 50, 5, 'BOOSTER', NULL, TRUE, CURRENT_TIMESTAMP)
ON CONFLICT (id) DO NOTHING;

SELECT setval('shop_items_id_seq', (SELECT MAX(id) FROM shop_items));

-- Ekwipunek uĹĽytkownika (User Items)
INSERT INTO user_items (user_id, item_id, is_equipped, acquired_at) VALUES
(1, 1, TRUE, CURRENT_TIMESTAMP - INTERVAL '3 days')
ON CONFLICT (user_id, item_id) DO NOTHING;

-- KsiÄ™ga walut (Currency Transactions)
INSERT INTO currency_transactions (user_id, currency, amount, balance_after, transaction_type, description, created_at) VALUES
(1, 'COINS', 50, 50, 'WELCOME_BONUS', 'Bonus powitalny za rejestracjÄ™', CURRENT_TIMESTAMP - INTERVAL '5 days'),
(1, 'COINS', 200, 250, 'STREAK_REWARD', 'Nagroda za 3-dniowÄ… seriÄ™ nauki', CURRENT_TIMESTAMP - INTERVAL '3 days'),
(1, 'COINS', -100, 150, 'SHOP_PURCHASE', 'Zakup przedmiotu: Awatary: MÄ…dra Sowa', CURRENT_TIMESTAMP - INTERVAL '3 days'),
(1, 'GEMS', 25, 25, 'ACHIEVEMENT', 'Nagroda za pierwszy rozwiÄ…zany test dzienny', CURRENT_TIMESTAMP - INTERVAL '1 day');
