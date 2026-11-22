<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Enums\LeitnerBox;

final class FlashcardProgress implements ModelInterface
{
    private ?int $id;
    private int $userId;
    private int $flashcardId;
    private LeitnerBox $box;
    private string $nextReviewAt;
    private int $consecutiveCorrect;
    private ?string $lastReviewedAt;

    public function __construct(
        ?int $id,
        int $userId,
        int $flashcardId,
        LeitnerBox $box = LeitnerBox::BOX_1,
        string $nextReviewAt = '',
        int $consecutiveCorrect = 0,
        ?string $lastReviewedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->flashcardId = $flashcardId;
        $this->box = $box;
        $this->nextReviewAt = $nextReviewAt;
        $this->consecutiveCorrect = $consecutiveCorrect;
        $this->lastReviewedAt = $lastReviewedAt;
    }

    public static function fromRow(array $row): self
    {
        $boxInt = isset($row['box']) ? (int) $row['box'] : 1;
        $box = LeitnerBox::tryFrom($boxInt) ?? LeitnerBox::BOX_1;

        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) ($row['user_id'] ?? 0),
            (int) ($row['flashcard_id'] ?? 0),
            $box,
            (string) ($row['next_review_at'] ?? ''),
            isset($row['consecutive_correct']) ? (int) $row['consecutive_correct'] : 0,
            isset($row['last_reviewed_at']) ? (string) $row['last_reviewed_at'] : null
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getFlashcardId(): int { return $this->flashcardId; }
    public function getBox(): LeitnerBox { return $this->box; }
    public function getNextReviewAt(): string { return $this->nextReviewAt; }
    public function getConsecutiveCorrect(): int { return $this->consecutiveCorrect; }
    public function getLastReviewedAt(): ?string { return $this->lastReviewedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'flashcard_id' => $this->flashcardId,
            'box' => $this->box->value,
            'next_review_at' => $this->nextReviewAt,
            'consecutive_correct' => $this->consecutiveCorrect,
            'last_reviewed_at' => $this->lastReviewedAt,
        ];
    }
}
