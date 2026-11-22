<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Enums\ItemCategory;

final class ShopItem implements ModelInterface
{
    private ?int $id;
    private string $name;
    private ?string $description;
    private int $priceCoins;
    private int $priceGems;
    private ItemCategory $category;
    private ?string $imagePath;
    private bool $isActive;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        string $name,
        ?string $description,
        int $priceCoins,
        int $priceGems,
        ItemCategory $category,
        ?string $imagePath = null,
        bool $isActive = true,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->priceCoins = $priceCoins;
        $this->priceGems = $priceGems;
        $this->category = $category;
        $this->imagePath = $imagePath;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
    }

    public static function fromRow(array $row): self
    {
        $cat = ItemCategory::tryFrom((string) ($row['category'] ?? 'AVATAR')) ?? ItemCategory::AVATAR;

        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['name'] ?? ''),
            isset($row['description']) ? (string) $row['description'] : null,
            isset($row['price_coins']) ? (int) $row['price_coins'] : 0,
            isset($row['price_gems']) ? (int) $row['price_gems'] : 0,
            $cat,
            isset($row['image_path']) ? (string) $row['image_path'] : null,
            !empty($row['is_active']),
            isset($row['created_at']) ? (string) $row['created_at'] : null
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getPriceCoins(): int { return $this->priceCoins; }
    public function getPriceGems(): int { return $this->priceGems; }
    public function getCategory(): ItemCategory { return $this->category; }
    public function getImagePath(): ?string { return $this->imagePath; }
    public function isActive(): bool { return $this->isActive; }
    public function getCreatedAt(): ?string { return $this->createdAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price_coins' => $this->priceCoins,
            'price_gems' => $this->priceGems,
            'category' => $this->category->value,
            'image_path' => $this->imagePath,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
        ];
    }
}
