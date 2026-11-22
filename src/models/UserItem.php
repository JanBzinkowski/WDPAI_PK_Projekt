<?php

declare(strict_types=1);

namespace App\Model;

final class UserItem implements ModelInterface
{
    private ?int $id;
    private int $userId;
    private int $itemId;
    private bool $isEquipped;
    private ?string $acquiredAt;

    public function __construct(
        ?int $id,
        int $userId,
        int $itemId,
        bool $isEquipped = false,
        ?string $acquiredAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->itemId = $itemId;
        $this->isEquipped = $isEquipped;
        $this->acquiredAt = $acquiredAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) ($row['user_id'] ?? 0),
            (int) ($row['item_id'] ?? 0),
            !empty($row['is_equipped']),
            isset($row['acquired_at']) ? (string) $row['acquired_at'] : null
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getItemId(): int { return $this->itemId; }
    public function isEquipped(): bool { return $this->isEquipped; }
    public function getAcquiredAt(): ?string { return $this->acquiredAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'item_id' => $this->itemId,
            'is_equipped' => $this->isEquipped,
            'acquired_at' => $this->acquiredAt,
        ];
    }
}
