<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security;

use KaririCode\Logging\Contract\AnonymizerStrategy;
use KaririCode\Logging\Security\Anonymizer\CreditCardAnonymizer;
use KaririCode\Logging\Security\Anonymizer\EmailAnonymizer;

class Anonymizer
{
    /** @var AnonymizerStrategy[] */
    private array $anonymizers;

    public function __construct(array $anonymizers = [])
    {
        $this->anonymizers = array_merge($this->getDefaultAnonymizers(), $anonymizers);
    }

    private function getDefaultAnonymizers(): array
    {
        return [
            'email' => new EmailAnonymizer(),
            'credit_card' => new CreditCardAnonymizer(),
        ];
    }

    public function anonymize(string $message): string
    {
        foreach ($this->anonymizers as $anonymizer) {
            if ($anonymizer instanceof AnonymizerStrategy) {
                $message = $anonymizer->anonymize($message);
            }
        }

        return $message;
    }

    public function addAnonymizer(string $name, AnonymizerStrategy $anonymizer): void
    {
        if ($this->isInvalidRegex($anonymizer->getPattern())) {
            throw new \InvalidArgumentException('Invalid regex pattern for type: ' . $name);
        }

        $this->anonymizers[$name] = $anonymizer;
    }

    public function removeAnonymizer(string $name): void
    {
        unset($this->anonymizers[$name]);
    }

    private function isInvalidRegex(string $pattern): bool
    {
        return false === @preg_match($pattern, '');
    }
}
