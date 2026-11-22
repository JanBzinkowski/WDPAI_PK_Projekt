<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Enums\Currency;

final class CurrencyTransaction implements ModelInterface
{
    private ?int $id;
    private int $userId;
    private Currency $currency;
    private int $amount;
    private int $balanceAfter;
    private string $transactionType;
    private ?string $description;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        int $userId,
        Currency $currency,
        int $amount,
        int $balanceAfter,
        string $transactionType,
        ?string $description = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->currency = $currency;
        $this->amount = $amount;
        $this->balanceAfter = $balanceAfter;
        $this->transactionType = $transactionType;
        $this->description = $description;
        $this->createdAt = $createdAt;
    }

    public static function fromRow(array $row): self
    {
        $curr = Currency::tryFrom((string) ($row['currency'] ?? 'COINS')) ?? Currency::COINS;

        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) ($row['user_id'] ?? 0),
            $curr,
            isset($row['amount']) ? (int) $row['amount'] : 0,
            isset($row['balance_after']) ? (int) $row['balance_after'] : 0,
            (string) ($row['transaction_type'] ?? 'REWARD'),
            isset($row['description']) ? (string) $row['description'] : null,
            isset($row['created_at']) ? (string) $row['created_at'] : null
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getCurrency(): Currency { return $this->currency; }
    public function getAmount(): int { return $this->amount; }
    public function getBalanceAfter(): int { return $this->balanceAfter; }
    public function getTransactionType(): string { return $this->transactionType; }
    public function getDescription(): ?string { return $this->description; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'currency' => $this->currency->value,
            'amount' => $this->amount,
            'balance_after' => $this->balanceAfter,
            'transaction_type' => $this->transactionType,
            'description' => $this->description,
            'created_at' => $this->createdAt,
        ];
    }
}
