<?php

declare(strict_types=1);

namespace App\Repository;

final class PgUserStatsRepository extends BaseRepository implements UserStatsRepositoryInterface
{
    public function getStatsForUser(int $userId): ?array
    {
        return $this->fetchOne('SELECT * FROM v_user_stats WHERE user_id = :user_id', [
            'user_id' => $userId,
        ]);
    }
}
