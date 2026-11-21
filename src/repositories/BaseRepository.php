<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;
use PDOStatement;

abstract class BaseRepository implements RepositoryInterface
{
    protected Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    protected function getConnection(): PDO
    {
        return $this->database->getConnection();
    }

    /**
     * @param array<string|int, mixed> $params
     * @return array<string, mixed>|null
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepareAndExecute($sql, $params);
        $result = $stmt->fetch();

        return is_array($result) ? $result : null;
    }

    /**
     * @param array<string|int, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * @param array<string|int, mixed> $params
     */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->rowCount();
    }

    protected function lastInsertId(?string $sequence = null): string
    {
        return (string) $this->getConnection()->lastInsertId($sequence);
    }

    /**
     * @template T
     * @param callable(PDO): T $operation
     * @return T
     */
    protected function transactional(callable $operation): mixed
    {
        return $this->database->transactional($operation);
    }

    /**
     * @param array<string|int, mixed> $params
     */
    private function prepareAndExecute(string $sql, array $params): PDOStatement
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
}
