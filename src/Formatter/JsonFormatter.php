<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\ImmutableValue;

class JsonFormatter extends AbstractFormatter
{
    public function format(ImmutableValue $record): string
    {
        $data = [
            'datetime' => $record->datetime->format($this->dateFormat),
            'level' => $record->level->value,
            'message' => $record->message,
        ];

        if (!empty($record->context)) {
            $data['context'] = $record->context;
        }

        return json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    public function formatBatch(array $records): string
    {
        return json_encode(array_map(function ($record) {
            return json_decode($this->format($record), true);
        }, $records), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
