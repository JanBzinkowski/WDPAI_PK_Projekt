<?php

declare(strict_types=1);

namespace App\Model;

final class Flashcard implements ModelInterface
{
    private ?int $id;
    private int $deckId;
    private string $front;
    private string $back;
    private ?string $hint;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        int $deckId,
        string $front,
        string $back,
        ?string $hint = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->deckId = $deckId;
        $this->front = $front;
        $this->back = $back;
        $this->hint = $hint;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) ($row['deck_id'] ?? 0),
            (string) ($row['front'] ?? ''),
            (string) ($row['back'] ?? ''),
            isset($row['hint']) ? (string) $row['hint'] : null,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getDeckId(): int { return $this->deckId; }
    public function getFront(): string { return $this->front; }
    public function getBack(): string { return $this->back; }
    public function getHint(): ?string { return $this->hint; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'deck_id' => $this->deckId,
            'front' => $this->front,
            'back' => $this->back,
            'hint' => $this->hint,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
