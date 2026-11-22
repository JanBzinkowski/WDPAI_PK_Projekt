<?php

declare(strict_types=1);

namespace App\Model;

final class DailyActivity implements ModelInterface
{
    private ?int $id;
    private int $userId;
    private string $activityDate;
    private int $reviewsCount;
    private bool $goalAchieved;
    private int $streakDays;

    public function __construct(
        ?int $id,
        int $userId,
        string $activityDate,
        int $reviewsCount = 0,
        bool $goalAchieved = false,
        int $streakDays = 0
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->activityDate = $activityDate;
        $this->reviewsCount = $reviewsCount;
        $this->goalAchieved = $goalAchieved;
        $this->streakDays = $streakDays;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) ($row['user_id'] ?? 0),
            (string) ($row['activity_date'] ?? date('Y-m-d')),
            isset($row['reviews_count']) ? (int) $row['reviews_count'] : 0,
            !empty($row['goal_achieved']),
            isset($row['streak_days']) ? (int) $row['streak_days'] : 0
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getActivityDate(): string { return $this->activityDate; }
    public function getReviewsCount(): int { return $this->reviewsCount; }
    public function isGoalAchieved(): bool { return $this->goalAchieved; }
    public function getStreakDays(): int { return $this->streakDays; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'activity_date' => $this->activityDate,
            'reviews_count' => $this->reviewsCount,
            'goal_achieved' => $this->goalAchieved,
            'streak_days' => $this->streakDays,
        ];
    }
}
