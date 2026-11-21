<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Config;
use App\Core\Database;
use App\Repository\BaseRepository;
use PDO;
use PHPUnit\Framework\TestCase;

class DummyRepository extends BaseRepository
{
    public function findUserById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM test_users WHERE id = :id', ['id' => $id]);
    }

    public function allUsers(): array
    {
        return $this->fetchAll('SELECT * FROM test_users ORDER BY id ASC');
    }

    public function insertUser(int $id, string $name): int
    {
        return $this->execute('INSERT INTO test_users (id, name) VALUES (:id, :name)', [
            'id' => $id,
            'name' => $name,
        ]);
    }

    public function performTransaction(callable $op): mixed
    {
        return $this->transactional($op);
    }
}

final class DatabaseTest extends TestCase
{
    private PDO $sqlitePdo;
    private Database $database;
    private DummyRepository $repository;

    protected function setUp(): void
    {
        $this->sqlitePdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->sqlitePdo->exec('CREATE TABLE test_users (id INTEGER PRIMARY KEY, name TEXT)');

        $config = new Config('dev', true, 'http://localhost:8080', 'UTC', 8080);
        $this->database = new Database($config, $this->sqlitePdo);
        $this->repository = new DummyRepository($this->database);
    }

    public function testPreparedInsertAndFetch(): void
    {
        $rows = $this->repository->insertUser(1, 'Alice');
        $this->assertSame(1, $rows);

        $user = $this->repository->findUserById(1);
        $this->assertNotNull($user);
        $this->assertSame('Alice', $user['name']);

        $all = $this->repository->allUsers();
        $this->assertCount(1, $all);
    }

    public function testTransactionRollbackOnException(): void
    {
        $this->repository->insertUser(1, 'Alice');

        try {
            $this->repository->performTransaction(function (PDO $pdo) {
                $pdo->exec("INSERT INTO test_users (id, name) VALUES (2, 'Bob')");
                throw new \RuntimeException('Failure inside transaction');
            });
        } catch (\RuntimeException $e) {
            $this->assertSame('Failure inside transaction', $e->getMessage());
        }

        $bob = $this->repository->findUserById(2);
        $this->assertNull($bob);
    }

    public function testTransactionCommitSuccess(): void
    {
        $result = $this->repository->performTransaction(function (PDO $pdo) {
            $pdo->exec("INSERT INTO test_users (id, name) VALUES (3, 'Charlie')");
            return 'committed';
        });

        $this->assertSame('committed', $result);
        $charlie = $this->repository->findUserById(3);
        $this->assertNotNull($charlie);
        $this->assertSame('Charlie', $charlie['name']);
    }
}
