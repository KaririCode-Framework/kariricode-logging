<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\ImmutableValue;

class ElasticFormatter extends AbstractFormatter
{
    public function format(ImmutableValue $record): string
    {
        $data = [
            '@timestamp' => $record->datetime->format('c'),
            'message' => $record->message,
            'level' => $record->level->value,
            'context' => $record->context,
        ];

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
