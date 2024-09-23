<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Formatter;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\Formatter\AbstractFormatter;
use PHPUnit\Framework\TestCase;

final class AbstractFormatterTest extends TestCase
{
    public function testGetFormatter(): void
    {
        $formatter = $this->createMock(AbstractFormatter::class);
        $this->assertInstanceOf(ImmutableValue::class, $formatter->getFormatter());
    }

    public function testToArray(): void
    {
        $formatterMock = $this->createMock(AbstractFormatter::class, ['Y-m-d H:i:s']);
        $formatterMock->expects($this->any())
            ->method('toArray')
            ->willReturn([
                'dateFormat' => 'Y-m-d H:i:s',
                'formatter' => $formatterMock,
            ]);

        $this->assertEquals([
            'dateFormat' => 'Y-m-d H:i:s',
            'formatter' => $formatterMock,
        ], $formatterMock->toArray());
    }
}
