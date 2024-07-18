<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Util;

use KaririCode\Logging\Util\Serializer;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new Serializer();
    }

    public function testSerializeUnserialize(): void
    {
        $data = ['key' => 'value'];

        $serialized = $this->serializer->serialize($data);
        $unserialized = $this->serializer->unserialize($serialized);

        $this->assertEquals($data, $unserialized);
    }

    public function testJsonSerializeUnserialize(): void
    {
        $data = ['key' => 'value'];

        $serialized = $this->serializer->jsonSerialize($data);
        $unserialized = $this->serializer->jsonUnserialize($serialized);

        $this->assertEquals($data, $unserialized);
    }
}
