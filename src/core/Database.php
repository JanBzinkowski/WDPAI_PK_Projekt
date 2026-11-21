<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

class Database
{
    private Config $config;
    private ?PDO $pdo = null;

    public function __construct(Config $config, ?PDO $pdo = null)
    {
        $this->config = $config;
        $this->pdo = $pdo;
    }

    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $this->config->getDbHost(),
                $this->config->getDbPort(),
                $this->config->getDbName()
            );

            $this->pdo = new PDO(
                $dsn,
                $this->config->getDbUser(),
                $this->config->getDbPassword(),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return $this->pdo;
    }

    public function setConnection(PDO $pdo): void
    {
        $this->pdo = $pdo;
    }

    /**
     * @template T
     * @param callable(PDO): T $operation
     * @return T
     * @throws Throwable
     */
    public function transactional(callable $operation): mixed
    {
        $pdo = $this->getConnection();

        if ($pdo->inTransaction()) {
            return $operation($pdo);
        }

        $pdo->beginTransaction();

        try {
            $result = $operation($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
