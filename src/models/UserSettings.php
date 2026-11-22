<?php

declare(strict_types=1);

namespace App\Model;

final class UserSettings implements ModelInterface
{
    private ?int $id;
    private int $userId;
    private int $dailyGoal;
    private bool $soundEnabled;
    private string $theme;
    private string $timezone;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        int $userId,
        int $dailyGoal = 10,
        bool $soundEnabled = true,
        string $theme = 'dark',
        string $timezone = 'Europe/Warsaw',
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->dailyGoal = $dailyGoal;
        $this->soundEnabled = $soundEnabled;
        $this->theme = $theme;
        $this->timezone = $timezone;
        $this->updatedAt = $updatedAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) ($row['user_id'] ?? 0),
            isset($row['daily_goal']) ? (int) $row['daily_goal'] : 10,
            !empty($row['sound_enabled']),
            (string) ($row['theme'] ?? 'dark'),
            (string) ($row['timezone'] ?? 'Europe/Warsaw'),
            isset($row['updated_at']) ? (string) $row['updated_at'] : null
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getDailyGoal(): int { return $this->dailyGoal; }
    public function isSoundEnabled(): bool { return $this->soundEnabled; }
    public function getTheme(): string { return $this->theme; }
    public function getTimezone(): string { return $this->timezone; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'daily_goal' => $this->dailyGoal,
            'sound_enabled' => $this->soundEnabled,
            'theme' => $this->theme,
            'timezone' => $this->timezone,
            'updated_at' => $this->updatedAt,
        ];
    }
}
