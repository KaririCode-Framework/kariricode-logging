<?php

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Security\Anonymizer;

class AnonymizerProcessor extends AbstractProcessor
{
    public function __construct(private Anonymizer $anonymizer)
    {
    }

    public function process(ImmutableValue $record): ImmutableValue
    {
        $anonymizedMessage = $this->anonymizer->anonymize($record->message);

        return new LogRecord(
            $record->level,
            $anonymizedMessage,
            $record->context,
            $record->datetime
        );
    }
}
