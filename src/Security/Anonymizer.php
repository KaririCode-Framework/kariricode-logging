<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security;

class Anonymizer
{
    private array $patterns = [
        'email' => '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
        'ip' => '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',
        'credit_card' => '/\b(?:\d{4}[-\s]?){3}\d{4}\b/',
    ];

    public function anonymize(string $message): string
    {
        foreach ($this->patterns as $type => $pattern) {
            $message = preg_replace_callback($pattern, function ($match) use ($type) {
                return $this->mask($match[0], $type);
            }, $message);
        }

        return $message;
    }

    private function mask(string $value, string $type): string
    {
        switch ($type) {
            case 'email':
                [$username, $domain] = explode('@', $value);
                $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);

                return $maskedUsername . '@' . $domain;
            case 'ip':
                return preg_replace('/\d/', '*', $value);
            case 'credit_card':
                $cleanNumber = preg_replace('/[^0-9]/', '', $value);
                $masked = str_repeat('*', strlen($cleanNumber) - 4) . substr($cleanNumber, -4);

                return preg_replace('/(.{4})/', '$1-', substr($masked, 0, -4)) . substr($masked, -4);

            default:
                return preg_replace('/\S/', '*', $value);
        }
    }

    public function addPattern(string $name, string $pattern): void
    {
        // Validate the pattern before adding it
        if ($this->isInvalidRegex($pattern)) {
            throw new \InvalidArgumentException('Invalid regex pattern for type: invalid');
        }

        $this->patterns[$name] = $pattern;
    }

    public function removePattern(string $name): void
    {
        unset($this->patterns[$name]);
    }

    private function isInvalidRegex(string $pattern): bool
    {
        return false === @preg_match($pattern, '');
    }
}
