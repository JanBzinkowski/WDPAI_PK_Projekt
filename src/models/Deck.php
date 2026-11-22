<?php

declare(strict_types=1);

namespace App\Model;

final class Deck implements ModelInterface
{
    private ?int $id;
    private int $userId;
    private string $title;
    private ?string $description;
    private bool $isPublic;
    private int $flashcardCount;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        int $userId,
        string $title,
        ?string $description = null,
        bool $isPublic = false,
        int $flashcardCount = 0,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->isPublic = $isPublic;
        $this->flashcardCount = $flashcardCount;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) ($row['user_id'] ?? 0),
            (string) ($row['title'] ?? ''),
            isset($row['description']) ? (string) $row['description'] : null,
            !empty($row['is_public']),
            isset($row['flashcard_count']) ? (int) $row['flashcard_count'] : 0,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function isPublic(): bool { return $this->isPublic; }
    public function getFlashcardCount(): int { return $this->flashcardCount; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'title' => $this->title,
            'description' => $this->description,
            'is_public' => $this->isPublic,
            'flashcard_count' => $this->flashcardCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
