<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Database;
use App\Repository\PgUserStatsRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class UserStatsRepositoryTest extends TestCase
{
    private PDO $sqlitePdo;
    private PgUserStatsRepository $repository;

    protected function setUp(): void
    {
        $this->sqlitePdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->sqlitePdo->exec("
            CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT, username TEXT, role TEXT, coins INTEGER, gems INTEGER);
            CREATE TABLE decks (id INTEGER PRIMARY KEY, user_id INTEGER);
            CREATE TABLE user_flashcard_progress (id INTEGER PRIMARY KEY, user_id INTEGER, box INTEGER);
            CREATE TABLE daily_activity (id INTEGER PRIMARY KEY, user_id INTEGER, reviews_count INTEGER, streak_days INTEGER);

            CREATE VIEW v_user_stats AS
            SELECT 
                u.id AS user_id,
                u.username,
                u.email,
                u.role,
                u.coins,
                u.gems,
                COUNT(DISTINCT d.id) AS total_decks,
                COUNT(DISTINCT p.id) AS total_cards_learned,
                COUNT(DISTINCT CASE WHEN p.box = 1 THEN p.id END) AS box_1_count,
                COUNT(DISTINCT CASE WHEN p.box = 2 THEN p.id END) AS box_2_count,
                COUNT(DISTINCT CASE WHEN p.box = 3 THEN p.id END) AS box_3_count,
                COUNT(DISTINCT CASE WHEN p.box = 4 THEN p.id END) AS box_4_count,
                COUNT(DISTINCT CASE WHEN p.box = 5 THEN p.id END) AS box_5_count,
                COALESCE(MAX(a.streak_days), 0) AS current_streak,
                COALESCE(SUM(a.reviews_count), 0) AS total_reviews_done
            FROM users u
            LEFT JOIN decks d ON d.user_id = u.id
            LEFT JOIN user_flashcard_progress p ON p.user_id = u.id
            LEFT JOIN daily_activity a ON a.user_id = u.id
            GROUP BY u.id, u.username, u.email, u.role, u.coins, u.gems;
        ");

        $this->sqlitePdo->exec("INSERT INTO users VALUES (1, 'jan@example.com', 'jan', 'USER', 150, 20)");
        $this->sqlitePdo->exec("INSERT INTO decks VALUES (10, 1), (11, 1)");
        $this->sqlitePdo->exec("INSERT INTO user_flashcard_progress VALUES (100, 1, 1), (101, 1, 3)");
        $this->sqlitePdo->exec("INSERT INTO daily_activity VALUES (1, 1, 15, 5)");

        $config = new Config('dev', true, 'http://localhost', 'UTC', 8080);
        $db = new Database($config, $this->sqlitePdo);
        $this->repository = new PgUserStatsRepository($db);
    }

    public function testGetStatsForUser(): void
    {
        $stats = $this->repository->getStatsForUser(1);

        $this->assertNotNull($stats);
        $this->assertSame(1, (int) $stats['user_id']);
        $this->assertSame('jan', $stats['username']);
        $this->assertSame(2, (int) $stats['total_decks']);
        $this->assertSame(2, (int) $stats['total_cards_learned']);
        $this->assertSame(1, (int) $stats['box_1_count']);
        $this->assertSame(1, (int) $stats['box_3_count']);
        $this->assertSame(0, (int) $stats['box_2_count']);
        $this->assertSame(5, (int) $stats['current_streak']);
    }
}
