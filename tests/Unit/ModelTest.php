<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Model\Enums\Currency;
use App\Model\Enums\ItemCategory;
use App\Model\Enums\LeitnerBox;
use App\Model\Enums\Role;
use App\Model\Flashcard;
use App\Model\FlashcardProgress;
use App\Model\ShopItem;
use App\Model\User;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testUserCreationAndFromRow(): void
    {
        $row = [
            'id' => '1',
            'email' => 'admin@demo.pl',
            'password_hash' => 'hash123',
            'username' => 'administrator',
            'role' => 'ADMIN',
            'coins' => '250',
            'gems' => '50',
            'created_at' => '2026-11-21 10:00:00',
        ];

        $user = User::fromRow($row);

        $this->assertSame(1, $user->getId());
        $this->assertSame('admin@demo.pl', $user->getEmail());
        $this->assertSame('administrator', $user->getUsername());
        $this->assertSame(Role::ADMIN, $user->getRole());
        $this->assertTrue($user->isAdmin());
        $this->assertSame(250, $user->getCoins());
        $this->assertSame(50, $user->getGems());

        $array = $user->toArray();
        $this->assertSame('ADMIN', $array['role']);
    }

    public function testFlashcardAndProgress(): void
    {
        $card = Flashcard::fromRow([
            'id' => 10,
            'deck_id' => 2,
            'front' => 'Question',
            'back' => 'Answer',
            'hint' => 'Hint text',
        ]);

        $this->assertSame(10, $card->getId());
        $this->assertSame('Question', $card->getFront());

        $progress = FlashcardProgress::fromRow([
            'id' => 100,
            'user_id' => 1,
            'flashcard_id' => 10,
            'box' => 3,
            'consecutive_correct' => 4,
        ]);

        $this->assertSame(LeitnerBox::BOX_3, $progress->getBox());
        $this->assertSame(3, $progress->getBox()->value);
        $this->assertSame(4, $progress->getConsecutiveCorrect());
    }

    public function testShopItemWithEnumCategory(): void
    {
        $item = ShopItem::fromRow([
            'id' => 5,
            'name' => 'Złoty Smok',
            'price_coins' => 100,
            'price_gems' => 5,
            'category' => 'AVATAR',
            'is_active' => 1,
        ]);

        $this->assertSame(ItemCategory::AVATAR, $item->getCategory());
        $this->assertSame(100, $item->getPriceCoins());
        $this->assertTrue($item->isActive());
    }
}
