<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\ImmutableValue;

class JsonFormatter extends AbstractFormatter
{
    private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function format(ImmutableValue $record): string
    {
        $data = $this->prepareData($record);

        return $this->encodeJson($data);
    }

    public function formatBatch(array $records): string
    {
        $formattedRecords = array_map([$this, 'prepareData'], $records);

        return $this->encodeJson($formattedRecords);
    }

    private function prepareData(ImmutableValue $record): array
    {
        $data = [
            'datetime' => $record->datetime->format($this->dateFormat),
            'level' => $record->level->value,
            'message' => $record->message,
        ];

        if (!empty($record->context)) {
            $data['context'] = $record->context;
        }

        return $data;
    }

    private function encodeJson($data): string
    {
        return json_encode($data, self::JSON_OPTIONS | JSON_THROW_ON_ERROR);
    }
}
