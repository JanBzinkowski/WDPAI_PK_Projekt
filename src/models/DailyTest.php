<?php

declare(strict_types=1);

namespace App\Model;

final class DailyTest implements ModelInterface
{
    private ?int $id;
    private int $userId;
    private string $testDate;
    private int $score;
    private int $totalQuestions;
    private ?string $completedAt;

    public function __construct(
        ?int $id,
        int $userId,
        string $testDate,
        int $score = 0,
        int $totalQuestions = 10,
        ?string $completedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->testDate = $testDate;
        $this->score = $score;
        $this->totalQuestions = $totalQuestions;
        $this->completedAt = $completedAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) ($row['user_id'] ?? 0),
            (string) ($row['test_date'] ?? date('Y-m-d')),
            isset($row['score']) ? (int) $row['score'] : 0,
            isset($row['total_questions']) ? (int) $row['total_questions'] : 10,
            isset($row['completed_at']) ? (string) $row['completed_at'] : null
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTestDate(): string { return $this->testDate; }
    public function getScore(): int { return $this->score; }
    public function getTotalQuestions(): int { return $this->totalQuestions; }
    public function getCompletedAt(): ?string { return $this->completedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'test_date' => $this->testDate,
            'score' => $this->score,
            'total_questions' => $this->totalQuestions,
            'completed_at' => $this->completedAt,
        ];
    }
}
