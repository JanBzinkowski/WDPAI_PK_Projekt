<?php

declare(strict_types=1);

namespace App\Repository;

interface UserStatsRepositoryInterface extends RepositoryInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function getStatsForUser(int $userId): ?array;
}
