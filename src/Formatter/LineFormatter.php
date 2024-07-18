<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\ImmutableValue;

class LineFormatter extends AbstractFormatter
{
    public function format(ImmutableValue $record): string
    {
        $date = $record->datetime->format($this->dateFormat);
        $level = strtoupper($record->level->value);
        $message = $record->message;
        $context = !empty($record->context) ? json_encode(
            $record->context,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) : '';

        return $context ?
            "[$date] $level: $message $context" :
            "[$date] $level: $message";
    }
}
