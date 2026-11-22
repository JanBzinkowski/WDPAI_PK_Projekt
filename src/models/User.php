<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Enums\Role;

final class User implements ModelInterface
{
    private ?int $id;
    private string $email;
    private string $passwordHash;
    private string $username;
    private Role $role;
    private int $coins;
    private int $gems;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        ?int $id,
        string $email,
        string $passwordHash,
        string $username,
        Role $role = Role::USER,
        int $coins = 0,
        int $gems = 0,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->username = $username;
        $this->role = $role;
        $this->coins = $coins;
        $this->gems = $gems;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['email'] ?? ''),
            (string) ($row['password_hash'] ?? ''),
            (string) ($row['username'] ?? ''),
            Role::tryFrom((string) ($row['role'] ?? 'USER')) ?? Role::USER,
            isset($row['coins']) ? (int) $row['coins'] : 0,
            isset($row['gems']) ? (int) $row['gems'] : 0,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null,
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getUsername(): string { return $this->username; }
    public function getRole(): Role { return $this->role; }
    public function getCoins(): int { return $this->coins; }
    public function getGems(): int { return $this->gems; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'username' => $this->username,
            'role' => $this->role->value,
            'coins' => $this->coins,
            'gems' => $this->gems,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
