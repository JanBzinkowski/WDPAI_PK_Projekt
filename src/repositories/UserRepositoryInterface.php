<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;
use App\Model\UserSettings;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;

    public function create(User $user): User;

    public function createUserSettings(UserSettings $settings): UserSettings;
}
