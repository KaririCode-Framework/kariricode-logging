<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security\Anonymizer;

use KaririCode\Logging\Contract\AnonymizerStrategy;

class CreditCardAnonymizer implements AnonymizerStrategy
{
    private const CREDIT_CARD_PATTERN = '/\b(?:\d{4}[-\s]?){3}\d{4}\b/';

    public function anonymize(string $value): string
    {
        return preg_replace_callback(self::CREDIT_CARD_PATTERN, function ($matches) {
            return $this->mask($matches[0]);
        }, $value);
    }

    public function mask(string $creditCard): string
    {
        $cleanNumber = preg_replace('/[^0-9]/', '', $creditCard);
        $masked = str_repeat('*', strlen($cleanNumber) - 4) . substr($cleanNumber, -4);

        return preg_replace('/(.{4})/', '$1-', substr($masked, 0, -4)) . substr($masked, -4);
    }

    public function getPattern(): string
    {
        return self::CREDIT_CARD_PATTERN;
    }
}
