<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Translator;
use App\Core\Validator;
use App\Exception\ValidationException;
use App\Model\Enums\Role;
use App\Model\User;
use App\Model\UserSettings;
use App\Repository\UserRepositoryInterface;

final class AuthService implements ServiceInterface
{
    private UserRepositoryInterface $userRepository;
    private ?Translator $translator;
    private LoginRateLimiter $rateLimiter;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ?Translator $translator = null,
        ?LoginRateLimiter $rateLimiter = null
    ) {
        $this->userRepository = $userRepository;
        $this->translator = $translator;
        $this->rateLimiter = $rateLimiter ?? new LoginRateLimiter();
    }

    /**
     * @throws \App\Exception\UnauthorizedException
     * @throws ValidationException
     */
    public function authenticate(string $email, string $password, string $ip = '127.0.0.1'): User
    {
        $email = trim($email);

        if ($this->rateLimiter->isTooManyAttempts($email, $ip)) {
            throw new ValidationException(
                ['email' => 'Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za 15 minut.'],
                'Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za 15 minut.'
            );
        }

        $user = $this->userRepository->findByEmail($email);
        $genericError = $this->translator !== null
            ? $this->translator->translate('auth.invalid_credentials')
            : 'Nieprawidłowy e-mail lub hasło.';

        if ($user === null || !password_verify($password, $user->getPasswordHash())) {
            $this->rateLimiter->hit($email, $ip);
            throw new \App\Exception\UnauthorizedException($genericError);
        }

        // Sprawdzenie, czy algorytm hashowania wymaga aktualizacji
        if (password_needs_rehash($user->getPasswordHash(), PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            // Zapis nowego hasha jeśli repozytorium to obsługuje
        }

        $this->rateLimiter->clear($email, $ip);
        return $user;
    }

    /**
     * @param array<string, mixed> $data
     * @throws ValidationException
     */
    public function register(array $data): User
    {
        $rules = [
            'email' => 'required|email|max:255',
            'username' => 'required|min:3|max:50',
            'password' => 'required|min:8|max:64',
            'password_confirmation' => 'required|same:password',
        ];

        $validator = Validator::make($data, $rules, $this->translator);
        $validated = $validator->validate();

        $existing = $this->userRepository->findByEmail($validated['email']);
        if ($existing !== null) {
            $msg = $this->translator !== null
                ? $this->translator->translate('auth.email_exists')
                : 'Użytkownik o podanym adresie e-mail już istnieje.';
            throw new ValidationException(['email' => $msg], $msg);
        }

        $passwordHash = password_hash((string) $validated['password'], PASSWORD_DEFAULT);

        $user = new User(
            null,
            (string) $validated['email'],
            $passwordHash,
            (string) $validated['username'],
            Role::USER,
            50, // Welcome bonus coins
            0
        );

        $createdUser = $this->userRepository->create($user);

        // Inicjalizacja domyślnych ustawień użytkownika
        if ($createdUser->getId() !== null) {
            $settings = new UserSettings(
                null,
                $createdUser->getId(),
                10,
                true,
                'dark',
                'Europe/Warsaw'
            );
            $this->userRepository->createUserSettings($settings);
        }

        return $createdUser;
    }

    public function getUserRepository(): UserRepositoryInterface
    {
        return $this->userRepository;
    }
}
