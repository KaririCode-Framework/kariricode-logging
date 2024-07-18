<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;

class FileHandler extends AbstractFileHandler
{
    public function handle(ImmutableValue $record): void
    {
        if ($record->level->value < $this->minLevel->value) {
            return;
        }

        $this->writeToFile($record);
    }
}
