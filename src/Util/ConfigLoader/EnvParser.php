<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util\ConfigLoader;

class EnvParser
{
    public function parse(string $value): mixed
    {
        $lowercaseValue = strtolower($value);

        return match ($lowercaseValue) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $this->parseNumericOrString($value),
        };
    }

    private function parseNumericOrString(string $value): int|float|string
    {
        if ($this->canParseAsInt($value)) {
            return $this->parseIntValue($value);
        }
        if ($this->canParseAsFloat($value)) {
            return $this->parseFloatValue($value);
        }

        return $this->parseStringValue($value);
    }

    public function parseIntValue(string $value): int
    {
        $result = filter_var($value, FILTER_VALIDATE_INT);
        if (false === $result) {
            throw new \InvalidArgumentException("Value '$value' cannot be parsed as integer.");
        }

        return $result;
    }

    public function parseFloatValue(string $value): float
    {
        $result = filter_var($value, FILTER_VALIDATE_FLOAT);
        if (false === $result) {
            throw new \InvalidArgumentException("Value '$value' cannot be parsed as float.");
        }

        return $result;
    }

    public function parseBooleanValue(string $value): bool
    {
        $lowercaseValue = strtolower($value);
        if (in_array($lowercaseValue, ['true', '(true)', '1', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array($lowercaseValue, ['false', '(false)', '0', 'no', 'off'], true)) {
            return false;
        }
        throw new \InvalidArgumentException("Value '$value' cannot be parsed as boolean.");
    }

    public function parseStringValue(string $value): string
    {
        return $value;
    }

    private function canParseAsInt(string $value): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_INT);
    }

    private function canParseAsFloat(string $value): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_FLOAT);
    }
}
