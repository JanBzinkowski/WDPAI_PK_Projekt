<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exception\ValidationException;
use App\Model\Enums\Role;
use App\Model\User;
use App\Model\UserSettings;
use App\Repository\UserRepositoryInterface;
use App\Service\AuthService;
use PHPUnit\Framework\TestCase;

class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<string, User> */
    public array $usersByEmail = [];
    public ?UserSettings $savedSettings = null;

    public function findByEmail(string $email): ?User
    {
        return $this->usersByEmail[strtolower($email)] ?? null;
    }

    public function findById(int $id): ?User
    {
        foreach ($this->usersByEmail as $user) {
            if ($user->getId() === $id) {
                return $user;
            }
        }
        return null;
    }

    public function create(User $user): User
    {
        $created = new User(
            count($this->usersByEmail) + 1,
            $user->getEmail(),
            $user->getPasswordHash(),
            $user->getUsername(),
            $user->getRole(),
            $user->getCoins(),
            $user->getGems(),
            '2026-11-22 12:00:00',
            '2026-11-22 12:00:00'
        );

        $this->usersByEmail[strtolower($user->getEmail())] = $created;
        return $created;
    }

    public function createUserSettings(UserSettings $settings): UserSettings
    {
        $this->savedSettings = $settings;
        return $settings;
    }
}

final class AuthServiceTest extends TestCase
{
    private InMemoryUserRepository $userRepo;
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->userRepo = new InMemoryUserRepository();
        $this->authService = new AuthService($this->userRepo);
    }

    public function testSuccessfulRegistrationHashesPassword(): void
    {
        $data = [
            'email' => 'newuser@example.com',
            'username' => 'newuser',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $user = $this->authService->register($data);

        $this->assertNotNull($user->getId());
        $this->assertSame('newuser@example.com', $user->getEmail());
        $this->assertSame(Role::USER, $user->getRole());
        $this->assertNotSame('SecurePass123!', $user->getPasswordHash());
        $this->assertTrue(password_verify('SecurePass123!', $user->getPasswordHash()));

        $this->assertNotNull($this->userRepo->savedSettings);
        $this->assertSame($user->getId(), $this->userRepo->savedSettings->getUserId());
    }

    public function testDuplicateEmailThrowsValidationException(): void
    {
        $existing = new User(1, 'taken@example.com', 'hash', 'taken');
        $this->userRepo->usersByEmail['taken@example.com'] = $existing;

        $data = [
            'email' => 'taken@example.com',
            'username' => 'someone',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $this->expectException(ValidationException::class);
        $this->authService->register($data);
    }

    public function testPasswordMismatchThrowsValidationException(): void
    {
        $data = [
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
        ];

        $this->expectException(ValidationException::class);
        $this->authService->register($data);
    }
}
