<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\LogHandler;
use KaririCode\Logging\Handler\AbstractHandler;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\TestCase;

final class ConcreteHandler extends AbstractHandler
{
    public function handle(ImmutableValue $record): void
    {
        // Implementação de exemplo para o método abstrato
    }
}

final class AbstractHandlerTest extends TestCase
{
    private ConcreteHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new ConcreteHandler();
    }

    public function testConstructor()
    {
        $minLevel = LogLevel::INFO;
        $formatter = $this->createMock(LogFormatter::class);
        $handler = new ConcreteHandler($minLevel, $formatter);

        $this->assertInstanceOf(AbstractHandler::class, $handler);
        $this->assertEquals($minLevel, $this->getProtectedProperty($handler, 'minLevel'));
        $this->assertSame($formatter, $this->getProtectedProperty($handler, 'formatter'));
    }

    public function testSetFormatter()
    {
        $formatter = $this->createMock(LogFormatter::class);
        $result = $this->handler->setFormatter($formatter);

        $this->assertSame($this->handler, $result);
        $this->assertSame($formatter, $this->getProtectedProperty($this->handler, 'formatter'));
    }

    public function testIsHandling()
    {
        $record = new LogRecord(LogLevel::DEBUG, 'Test message');

        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('isHandling');
        $method->setAccessible(true);

        $this->assertTrue($method->invokeArgs($this->handler, [$record]));
    }

    public function testAddHandler()
    {
        $newHandler = $this->createMock(LogHandler::class);
        $result = $this->handler->addHandler($newHandler);

        $this->assertSame($this->handler, $result);
        $handlers = $this->getProtectedProperty($this->handler, 'handlers');
        $this->assertCount(1, $handlers);
        $this->assertSame($newHandler, $handlers[0]['handler']);
        $this->assertSame(LogLevel::DEBUG, $handlers[0]['level']);

        $customLevel = LogLevel::ERROR;
        $this->handler->addHandler($newHandler, $customLevel);
        $handlers = $this->getProtectedProperty($this->handler, 'handlers');
        $this->assertCount(2, $handlers);
        $this->assertSame($customLevel, $handlers[1]['level']);
    }

    public function testPushHandler()
    {
        $handler1 = $this->createMock(LogHandler::class);
        $handler2 = $this->createMock(LogHandler::class);

        $this->handler->addHandler($handler1);
        $result = $this->handler->pushHandler($handler2);

        $this->assertSame($this->handler, $result);
        $handlers = $this->getProtectedProperty($this->handler, 'handlers');
        $this->assertCount(2, $handlers);
        $this->assertSame($handler2, $handlers[0]['handler']);
        $this->assertSame($handler1, $handlers[1]['handler']);
    }

    public function testPopHandler()
    {
        $handler1 = $this->createMock(LogHandler::class);
        $handler2 = $this->createMock(LogHandler::class);

        $this->handler->addHandler($handler1);
        $this->handler->addHandler($handler2);

        $poppedHandler = $this->handler->popHandler();
        $this->assertSame($handler1, $poppedHandler);

        $handlers = $this->getProtectedProperty($this->handler, 'handlers');
        $this->assertCount(1, $handlers);
        $this->assertSame($handler2, $handlers[0]['handler']);

        $poppedHandler = $this->handler->popHandler();
        $this->assertSame($handler2, $poppedHandler);

        $this->assertNull($this->handler->popHandler());
    }

    public function testGetHandlers()
    {
        $handler1 = $this->createMock(LogHandler::class);
        $handler2 = $this->createMock(LogHandler::class);

        $this->handler->addHandler($handler1);
        $this->handler->addHandler($handler2);

        $handlers = $this->handler->getHandlers();
        $this->assertCount(2, $handlers);
        $this->assertSame($handler1, $handlers[0]);
        $this->assertSame($handler2, $handlers[1]);
    }

    private function getProtectedProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);

        return $reflection_property->getValue($object);
    }
}
