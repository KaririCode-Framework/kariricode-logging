<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;

class NullHandler extends AbstractHandler
{
    public function handle(ImmutableValue $record): void
    {
        // Do nothing
    }
}
