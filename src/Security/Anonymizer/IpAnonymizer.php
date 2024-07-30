<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security\Anonymizer;

use KaririCode\Logging\Contract\AnonymizerStrategy;

class IpAnonymizer implements AnonymizerStrategy
{
    private const IP_PATTERN = '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/';

    public function anonymize(string $value): string
    {
        return preg_replace_callback(self::IP_PATTERN, function ($matches) {
            return $this->mask($matches[0]);
        }, $value);
    }

    public function mask(string $ip): string
    {
        return '***.***.***.***';
    }

    public function getPattern(): string
    {
        return self::IP_PATTERN;
    }
}
