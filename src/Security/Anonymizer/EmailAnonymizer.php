<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security\Anonymizer;

use KaririCode\Logging\Contract\AnonymizerStrategy;

class EmailAnonymizer implements AnonymizerStrategy
{
    private const EMAIL_PATTERN = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';

    public function anonymize(string $value): string
    {
        return preg_replace_callback(self::EMAIL_PATTERN, function ($matches) {
            return $this->mask($matches[0]);
        }, $value);
    }

    public function mask(string $email): string
    {
        [$username, $domain] = explode('@', $email);
        $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);

        return $maskedUsername . '@' . $domain;
    }

    public function getPattern(): string
    {
        return self::EMAIL_PATTERN;
    }
}
