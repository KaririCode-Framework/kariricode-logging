<?php

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Security\Encryptor;

class EncryptionProcessor extends AbstractProcessor
{
    private Encryptor $encryptor;

    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function process(ImmutableValue $record): ImmutableValue
    {
        $encryptedMessage = $this->encryptor->encrypt($record->message);

        return new LogRecord($record->level, $encryptedMessage, $record->context, $record->datetime);
    }
}
