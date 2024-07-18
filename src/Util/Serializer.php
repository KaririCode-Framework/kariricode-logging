<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

class Serializer
{
    public function serialize(mixed $data): string
    {
        return serialize($data);
    }

    public function unserialize(string $data): mixed
    {
        return unserialize($data);
    }

    public function jsonSerialize(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public function jsonUnserialize(string $data): mixed
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }
}
