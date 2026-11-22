-- =============================================================================
-- Flashcards Leitner App - Physical Database Schema (PostgreSQL 16)
-- Zgodne z zadaniem KAN-32, model 3NF, klucze, ograniczenia CHECK i indeksy
-- =============================================================================

-- Upewnienie się o czystości schematu przy inicjalizacji
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

-- 1. Tabela użytkowników (users)
-- Przechowuje konta, hasła, rolę RBAC oraz aktualne saldo walut
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

-- 2. Tabela ustawień użytkownika (user_settings)
-- ON DELETE CASCADE: usunięcie profilu użytkownika usuwa jego ustawienia
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
-- ON DELETE CASCADE: usunięcie konta autora usuwa stworzone przez niego talie
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
-- ON DELETE CASCADE: usunięcie talii automatycznie usuwa wszystkie jej fiszki
CREATE TABLE flashcards (
    id SERIAL PRIMARY KEY,
    deck_id INT NOT NULL REFERENCES decks(id) ON DELETE CASCADE,
    front TEXT NOT NULL,
    back TEXT NOT NULL,
    hint TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabela indywidualnego postępu Leitnera (user_flashcard_progress)
-- Każdy użytkownik uczy się niezależnie (UNIQUE user_id + flashcard_id)
-- Pudełka Leitnera od 1 do 5 (CHECK box BETWEEN 1 AND 5)
-- ON DELETE CASCADE: usunięcie fiszki lub użytkownika czyści powiązany postęp
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
-- ON DELETE CASCADE: powiązana z użytkownikiem i talią
CREATE TABLE study_sessions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    deck_id INT NOT NULL REFERENCES decks(id) ON DELETE CASCADE,
    started_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    finished_at TIMESTAMP WITH TIME ZONE NULL,
    total_reviewed INT NOT NULL DEFAULT 0 CHECK (total_reviewed >= 0),
    correct_count INT NOT NULL DEFAULT 0 CHECK (correct_count >= 0)
);

-- 7. Tabela audytu powtórek (review_logs)
-- ON DELETE SET NULL: usunięcie sesji lub fiszki zachowuje statystyki powtórek użytkownika
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

-- 8. Tabela księgi walut (currency_transactions)
-- Audyt każdej zmiany stanu monet i klejnotów użytkownika
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

-- 9. Tabela codziennej aktywności i serii (daily_activity)
-- Zabezpiecza unikalność wpisu na dany dzień per użytkownik
CREATE TABLE daily_activity (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    activity_date DATE NOT NULL DEFAULT CURRENT_DATE,
    reviews_count INT NOT NULL DEFAULT 0 CHECK (reviews_count >= 0),
    goal_achieved BOOLEAN NOT NULL DEFAULT FALSE,
    streak_days INT NOT NULL DEFAULT 0 CHECK (streak_days >= 0),
    CONSTRAINT uq_user_activity_date UNIQUE (user_id, activity_date)
);

-- 10. Tabela codziennych testów wiedzy (daily_tests)
CREATE TABLE daily_tests (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    test_date DATE NOT NULL DEFAULT CURRENT_DATE,
    score INT NOT NULL DEFAULT 0 CHECK (score >= 0),
    total_questions INT NOT NULL DEFAULT 10 CHECK (total_questions > 0),
    completed_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_user_test_date UNIQUE (user_id, test_date)
);

-- 11. Tabela przedmiotów w sklepie (shop_items)
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

-- 12. Ekwipunek użytkownika (user_items)
-- ON DELETE RESTRICT: uniemożliwia skasowanie przedmiotu sklepu, jeśli został już zakupiony
CREATE TABLE user_items (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    item_id INT NOT NULL REFERENCES shop_items(id) ON DELETE RESTRICT,
    is_equipped BOOLEAN NOT NULL DEFAULT FALSE,
    acquired_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_user_item UNIQUE (user_id, item_id)
);

-- 13. Tokeny resetu hasła (password_resets)
CREATE TABLE password_resets (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    used_at TIMESTAMP WITH TIME ZONE NULL,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =============================================================================
-- Indeksy wydajnościowe
-- =============================================================================
CREATE INDEX idx_user_flashcard_progress_next_review ON user_flashcard_progress(next_review_at);
CREATE INDEX idx_user_flashcard_progress_user ON user_flashcard_progress(user_id);
CREATE INDEX idx_decks_is_public ON decks(is_public);
CREATE INDEX idx_decks_user_id ON decks(user_id);
CREATE INDEX idx_flashcards_deck_id ON flashcards(deck_id);
CREATE INDEX idx_review_logs_user_reviewed ON review_logs(user_id, reviewed_at);
CREATE INDEX idx_currency_transactions_user ON currency_transactions(user_id);

-- =============================================================================
-- Widok statystyk użytkownika (v_user_stats) - Zadanie KAN-33
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

