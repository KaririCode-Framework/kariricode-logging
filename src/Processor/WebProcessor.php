<?php

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;

class WebProcessor extends AbstractProcessor
{
    public function process(ImmutableValue $record): ImmutableValue
    {
        $server = $_SERVER;
        $context = array_merge($record->context, [
            'url' => ($server['HTTPS'] ?? 'off') === 'on' ? 'https://' : 'http://' .
                     ($server['HTTP_HOST'] ?? 'localhost') .
                     ($server['REQUEST_URI'] ?? '/'),
            'ip' => $server['REMOTE_ADDR'] ?? null,
            'http_method' => $server['REQUEST_METHOD'] ?? null,
            'server' => $server['SERVER_NAME'] ?? null,
            'referrer' => $server['HTTP_REFERER'] ?? null,
        ]);

        return new LogRecord(
            $record->level,
            $record->message,
            $context,
            $record->datetime,
            $record->extra
        );
    }
}
