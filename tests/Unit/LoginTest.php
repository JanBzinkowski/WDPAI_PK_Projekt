<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exception\UnauthorizedException;
use App\Exception\ValidationException;
use App\Model\Enums\Role;
use App\Model\User;
use App\Service\AuthService;
use App\Service\LoginRateLimiter;
use PHPUnit\Framework\TestCase;

final class LoginTest extends TestCase
{
    private InMemoryUserRepository $userRepo;
    private LoginRateLimiter $limiter;
    private AuthService $authService;

    protected function setUp(): void
    {
        LoginRateLimiter::reset();
        $this->userRepo = new InMemoryUserRepository();
        $this->limiter = new LoginRateLimiter(3, 60);
        $this->authService = new AuthService($this->userRepo, null, $this->limiter);

        $hash = password_hash('SecretPassword123!', PASSWORD_DEFAULT);
        $user = new User(1, 'valid@example.com', $hash, 'ValidUser', Role::USER);
        $this->userRepo->usersByEmail['valid@example.com'] = $user;
    }

    public function testSuccessfulAuthentication(): void
    {
        $authenticated = $this->authService->authenticate('valid@example.com', 'SecretPassword123!', '127.0.0.1');

        $this->assertSame(1, $authenticated->getId());
        $this->assertSame('valid@example.com', $authenticated->getEmail());
    }

    public function testInvalidPasswordReturnsGenericError(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Nieprawidłowy e-mail lub hasło.');

        $this->authService->authenticate('valid@example.com', 'WrongPassword!', '127.0.0.1');
    }

    public function testUnknownEmailReturnsGenericError(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Nieprawidłowy e-mail lub hasło.');

        $this->authService->authenticate('unknown@example.com', 'SomePassword!', '127.0.0.1');
    }

    public function testRateLimiterBlocksAfterExceedingAttempts(): void
    {
        // 3 failed attempts allowed
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->authService->authenticate('valid@example.com', 'BadPass' . $i, '192.168.1.100');
            } catch (UnauthorizedException) {}
        }

        // 4th attempt should be blocked by rate limiter
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Zbyt wiele nieudanych prób logowania');

        $this->authService->authenticate('valid@example.com', 'BadPass4', '192.168.1.100');
    }
}
