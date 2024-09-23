<?php

declare(strict_types=1);

namespace KaririCode\Logging\Contract;

interface AnonymizerStrategy
{
    public function anonymize(string $value): string;

    public function mask(string $value): string;

    public function getPattern(): string;
}
