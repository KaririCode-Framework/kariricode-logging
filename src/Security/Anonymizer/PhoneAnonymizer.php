<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security\Anonymizer;

use KaririCode\Logging\Contract\AnonymizerStrategy;

class PhoneAnonymizer implements AnonymizerStrategy
{
    private const PHONE_PATTERN = '/\+?\d{4,5}-?\d{4}/';

    public function anonymize(string $value): string
    {
        return preg_replace_callback(self::PHONE_PATTERN, function ($matches) {
            return $this->mask($matches[0]);
        }, $value);
    }

    public function mask(string $phone): string
    {
        return preg_replace('/(\d{4})-?(\d{2})/', '****-**', $phone);
    }

    public function getPattern(): string
    {
        return self::PHONE_PATTERN;
    }
}
