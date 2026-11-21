<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Translator;
use App\Core\Validator;
use App\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    private Translator $translator;

    protected function setUp(): void
    {
        $this->translator = new Translator('pl');
    }

    public function testValidDataPasses(): void
    {
        $data = [
            'email' => 'user@example.com',
            'password' => 'secret123',
            'age' => 25,
            'role' => 'user',
        ];

        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:8|max:32',
            'age' => 'required|between:1,120',
            'role' => 'required|in:user,admin',
        ];

        $validator = Validator::make($data, $rules, $this->translator);
        $validated = $validator->validate();

        $this->assertSame($data, $validated);
        $this->assertFalse($validator->fails());
    }

    public function testMissingRequiredThrowsValidationException(): void
    {
        $data = ['email' => ''];
        $rules = ['email' => 'required|email'];

        $validator = Validator::make($data, $rules, $this->translator);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }

    public function testTranslatedErrorMessagesInPolish(): void
    {
        $data = [
            'email' => 'invalid-email',
            'password' => 'short',
        ];
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];

        $validator = Validator::make($data, $rules, $this->translator);

        try {
            $validator->validate();
            $this->fail('Expected ValidationException not thrown');
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertSame('Pole email musi zawierać poprawny adres e-mail.', $errors['email']);
            $this->assertSame('Pole password musi mieć co najmniej 8 znaków.', $errors['password']);
        }
    }

    public function testTranslatedErrorMessagesInEnglish(): void
    {
        $enTranslator = new Translator('en');
        $data = ['email' => 'bad'];
        $rules = ['email' => 'email'];

        $validator = Validator::make($data, $rules, $enTranslator);

        try {
            $validator->validate();
            $this->fail('Expected ValidationException not thrown');
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertSame('The email field must be a valid email address.', $errors['email']);
        }
    }

    public function testSameRuleForPasswordConfirmation(): void
    {
        $data = [
            'password' => 'password123',
            'password_confirmation' => 'mismatch',
        ];
        $rules = [
            'password_confirmation' => 'same:password',
        ];

        $validator = Validator::make($data, $rules, $this->translator);

        try {
            $validator->validate();
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('password_confirmation', $e->getErrors());
        }
    }
}
