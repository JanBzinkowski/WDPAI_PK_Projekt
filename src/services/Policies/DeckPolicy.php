<?php

declare(strict_types=1);

namespace App\Service\Policies;

use App\Model\Deck;
use App\Model\User;

final class DeckPolicy
{
    public static function canView(User $user, Deck $deck): bool
    {
        if ($deck->isPublic()) {
            return true;
        }

        return self::isOwnerOrAdmin($user, $deck);
    }

    public static function canEdit(User $user, Deck $deck): bool
    {
        return self::isOwnerOrAdmin($user, $deck);
    }

    public static function canDelete(User $user, Deck $deck): bool
    {
        return self::isOwnerOrAdmin($user, $deck);
    }

    private static function isOwnerOrAdmin(User $user, Deck $deck): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->getId() !== null && $deck->getUserId() === $user->getId();
    }
}
