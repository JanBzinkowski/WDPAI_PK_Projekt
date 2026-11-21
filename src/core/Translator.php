<?php

declare(strict_types=1);

namespace App\Core;

final class Translator
{
    private string $locale;
    private string $langDir;
    /** @var array<string, array<string, mixed>> */
    private array $translations = [];

    public function __construct(string $locale = 'pl', ?string $langDir = null)
    {
        $this->locale = in_array($locale, ['pl', 'en'], true) ? $locale : 'pl';
        $this->langDir = $langDir ?? dirname(__DIR__, 2) . '/lang';
        $this->loadLocale($this->locale);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        if (in_array($locale, ['pl', 'en'], true)) {
            $this->locale = $locale;
            $this->loadLocale($locale);
        }
    }

    public function translate(string $key, array $replace = []): string
    {
        $keys = explode('.', $key);
        $text = $this->translations[$this->locale] ?? [];

        foreach ($keys as $segment) {
            if (!is_array($text) || !isset($text[$segment])) {
                return $key;
            }
            $text = $text[$segment];
        }

        if (!is_string($text)) {
            return $key;
        }

        foreach ($replace as $placeholder => $value) {
            $text = str_replace(':' . $placeholder, (string) $value, $text);
        }

        return $text;
    }

    private function loadLocale(string $locale): void
    {
        if (isset($this->translations[$locale])) {
            return;
        }

        $file = $this->langDir . '/' . $locale . '.php';
        if (is_file($file)) {
            $data = require $file;
            $this->translations[$locale] = is_array($data) ? $data : [];
        } else {
            $this->translations[$locale] = [];
        }
    }
}
