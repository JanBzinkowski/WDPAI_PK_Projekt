<?php

declare(strict_types=1);

namespace App\Core;

use App\Exception\ValidationException;
use UnitEnum;

final class Validator
{
    private array $data;
    /** @var array<string, string|array<string>> */
    private array $rules;
    /** @var array<string, string> */
    private array $errors = [];
    private ?Translator $translator;

    /**
     * @param array<string, mixed> $data
     * @param array<string, string|array<string>> $rules
     */
    public function __construct(array $data, array $rules, ?Translator $translator = null)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->translator = $translator;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string|array<string>> $rules
     */
    public static function make(array $data, array $rules, ?Translator $translator = null): self
    {
        return new self($data, $rules, $translator);
    }

    /**
     * @return array<string, mixed> Validated data
     * @throws ValidationException
     */
    public function validate(): array
    {
        foreach ($this->rules as $field => $fieldRules) {
            $ruleList = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
            $value = $this->data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, trim($rule));
            }
        }

        if (!empty($this->errors)) {
            throw new ValidationException($this->errors, 'Błędy walidacji danych wejściowych');
        }

        return $this->data;
    }

    public function fails(): bool
    {
        try {
            $this->validate();
            return false;
        } catch (ValidationException) {
            return true;
        }
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        if (isset($this->errors[$field])) {
            return;
        }

        if ($rule === 'required') {
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                $this->addError($field, 'validation.required', ['field' => $field]);
            }
            return;
        }

        // Jeśli wartość jest pusta i pole nie było required, pomijamy dalsze reguły
        if ($value === null || $value === '') {
            return;
        }

        if ($rule === 'email') {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->addError($field, 'validation.email', ['field' => $field]);
            }
            return;
        }

        if (str_starts_with($rule, 'min:')) {
            $min = (int) substr($rule, 4);
            if (mb_strlen((string) $value) < $min) {
                $this->addError($field, 'validation.min_length', ['field' => $field, 'min' => $min]);
            }
            return;
        }

        if (str_starts_with($rule, 'max:')) {
            $max = (int) substr($rule, 4);
            if (mb_strlen((string) $value) > $max) {
                $this->addError($field, 'validation.max_length', ['field' => $field, 'max' => $max]);
            }
            return;
        }

        if (str_starts_with($rule, 'between:')) {
            [$min, $max] = array_map('intval', explode(',', substr($rule, 8)));
            $intVal = filter_var($value, FILTER_VALIDATE_INT);
            if ($intVal === false || $intVal < $min || $intVal > $max) {
                $this->addError($field, 'validation.int_range', ['field' => $field, 'min' => $min, 'max' => $max]);
            }
            return;
        }

        if (str_starts_with($rule, 'in:')) {
            $allowed = array_map('trim', explode(',', substr($rule, 3)));
            if (!in_array((string) $value, $allowed, true)) {
                $this->addError($field, 'validation.in_enum', ['field' => $field]);
            }
            return;
        }

        if (str_starts_with($rule, 'enum:')) {
            $enumClass = substr($rule, 5);
            if (is_subclass_of($enumClass, UnitEnum::class)) {
                $cases = array_map(fn ($case) => $case->value ?? $case->name, $enumClass::cases());
                if (!in_array($value, $cases, true)) {
                    $this->addError($field, 'validation.in_enum', ['field' => $field]);
                }
            }
            return;
        }

        if (str_starts_with($rule, 'same:')) {
            $otherField = substr($rule, 5);
            $otherValue = $this->data[$otherField] ?? null;
            if ($value !== $otherValue) {
                $this->addError($field, 'validation.same', ['field' => $field, 'other' => $otherField]);
            }
            return;
        }
    }

    private function addError(string $field, string $translationKey, array $replace = []): void
    {
        if ($this->translator !== null) {
            $message = $this->translator->translate($translationKey, $replace);
        } else {
            $message = $translationKey;
        }

        $this->errors[$field] = $message;
    }
}
