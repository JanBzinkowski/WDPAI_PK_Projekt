<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;
use App\Model\UserSettings;
use PDO;

final class PgUserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        $row = $this->fetchOne('SELECT * FROM users WHERE LOWER(email) = LOWER(:email)', [
            'email' => $email,
        ]);

        return $row !== null ? User::fromRow($row) : null;
    }

    public function findById(int $id): ?User
    {
        $row = $this->fetchOne('SELECT * FROM users WHERE id = :id', [
            'id' => $id,
        ]);

        return $row !== null ? User::fromRow($row) : null;
    }

    public function create(User $user): User
    {
        return $this->transactional(function (PDO $pdo) use ($user): User {
            $stmt = $pdo->prepare('
                INSERT INTO users (email, password_hash, username, role, coins, gems, created_at, updated_at)
                VALUES (:email, :password_hash, :username, :role, :coins, :gems, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                RETURNING id, created_at, updated_at
            ');

            $stmt->execute([
                'email' => $user->getEmail(),
                'password_hash' => $user->getPasswordHash(),
                'username' => $user->getUsername(),
                'role' => $user->getRole()->value,
                'coins' => $user->getCoins(),
                'gems' => $user->getGems(),
            ]);

            $result = $stmt->fetch();
            $newId = (int) $result['id'];
            $createdAt = (string) $result['created_at'];
            $updatedAt = (string) $result['updated_at'];

            return new User(
                $newId,
                $user->getEmail(),
                $user->getPasswordHash(),
                $user->getUsername(),
                $user->getRole(),
                $user->getCoins(),
                $user->getGems(),
                $createdAt,
                $updatedAt
            );
        });
    }

    public function createUserSettings(UserSettings $settings): UserSettings
    {
        $this->execute('
            INSERT INTO user_settings (user_id, daily_goal, sound_enabled, theme, timezone, created_at, updated_at)
            VALUES (:user_id, :daily_goal, :sound_enabled, :theme, :timezone, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ON CONFLICT (user_id) DO NOTHING
        ', [
            'user_id' => $settings->getUserId(),
            'daily_goal' => $settings->getDailyGoal(),
            'sound_enabled' => $settings->isSoundEnabled() ? 1 : 0,
            'theme' => $settings->getTheme(),
            'timezone' => $settings->getTimezone(),
        ]);

        return $settings;
    }
}
