<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\KaririCode\Logging\Handler;

use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Logging\Formatter\ConsoleColorFormatter;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\Handler\ConsoleHandler;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConsoleHandlerTest extends TestCase
{
    private mixed $outputMock;
    private LogFormatter|MockObject $formatterMock;
    private ConsoleColorFormatter|MockObject $colorFormatterMock;

    protected function setUp(): void
    {
        $this->outputMock = fopen('php://memory', 'w+');
        $this->formatterMock = $this->createMock(LogFormatter::class);
        $this->colorFormatterMock = $this->createMock(ConsoleColorFormatter::class);
    }

    protected function tearDown(): void
    {
        if (is_resource($this->outputMock)) {
            fclose($this->outputMock);
        }
    }

    public function testConstructor(): void
    {
        $handler = new ConsoleHandler();
        $this->assertInstanceOf(ConsoleHandler::class, $handler);
        $this->assertInstanceOf(LineFormatter::class, $this->getPrivateProperty($handler, 'formatter'));
    }

    public function testConstructorWithCustomFormatter(): void
    {
        $customFormatter = $this->createMock(LogFormatter::class);
        $handler = new ConsoleHandler(LogLevel::INFO, true, $customFormatter);
        $this->assertSame($customFormatter, $this->getPrivateProperty($handler, 'formatter'));
    }

    public function testHandleWithColors(): void
    {
        $handler = $this->getHandlerWithMocks(true);

        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $this->formatterMock->expects($this->once())
            ->method('format')
            ->with($record)
            ->willReturn('Formatted message');

        $this->colorFormatterMock->expects($this->once())
            ->method('format')
            ->with($record->level, 'Formatted message')
            ->willReturn("\033[0;32mFormatted message\033[0m");

        $handler->handle($record);

        rewind($this->outputMock);
        $output = stream_get_contents($this->outputMock);
        $this->assertEquals("\033[0;32mFormatted message\033[0m\n", $output);
    }

    public function testHandleWithoutColors(): void
    {
        $handler = $this->getHandlerWithMocks(false);

        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $this->formatterMock->expects($this->once())
            ->method('format')
            ->with($record)
            ->willReturn('Formatted message');

        $this->colorFormatterMock->expects($this->never())
            ->method('format');

        $handler->handle($record);

        rewind($this->outputMock);
        $output = stream_get_contents($this->outputMock);
        $this->assertEquals("Formatted message\n", $output);
    }

    private function getHandlerWithMocks(bool $useColors): ConsoleHandler
    {
        $handler = new ConsoleHandler(LogLevel::DEBUG, $useColors, $this->formatterMock);
        $this->setPrivateProperty($handler, 'output', $this->outputMock);
        $this->setPrivateProperty($handler, 'colorFormatter', $this->colorFormatterMock);

        return $handler;
    }

    private function getPrivateProperty(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    private function setPrivateProperty(object $object, string $propertyName, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
