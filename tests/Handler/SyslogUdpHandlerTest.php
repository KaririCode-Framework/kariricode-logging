<?php

declare(strict_types=1);

declare(strict_types=1);

namespace KaririCode\Logging\KaririCode\Logging\Tests\Logging\Handler;

use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Logging\Handler\SyslogUdpHandler;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SyslogUdpHandlerTest extends TestCase
{
    private string $host = '127.0.0.1';
    private int $port = 514;

    protected function setUp(): void
    {
        if (!defined('AF_INET')) {
            define('AF_INET', 2);
            define('SOCK_DGRAM', 2);
            define('SOL_UDP', 17);
        }
    }

    public function testConstructor(): void
    {
        $handler = new SyslogUdpHandler($this->host, $this->port);
        $this->assertInstanceOf(SyslogUdpHandler::class, $handler);
    }

    public function testConstructorThrowsExceptionOnFailure(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to create socket');

        // Create a partial mock of the SyslogUdpHandler class
        /** @var SyslogUdpHandler|MockObject $mock */
        $mock = $this->getMockBuilder(SyslogUdpHandler::class)
            ->setConstructorArgs([$this->host, $this->port])
            ->onlyMethods(['createSocket'])
            ->getMock();

        // Configure the mock to return false when createSocket is called
        $mock->expects($this->once())
            ->method('createSocket')
            ->willReturn(false);

        // Trigger the constructor to test if the exception is thrown
        $mock->__construct($this->host, $this->port);
    }

    public function testGetSyslogPriority(): void
    {
        $handler = new SyslogUdpHandler($this->host, $this->port);
        $method = new \ReflectionMethod(SyslogUdpHandler::class, 'getSyslogPriority');
        $method->setAccessible(true);

        $this->assertEquals(135, $method->invoke($handler, LogLevel::DEBUG));
        $this->assertEquals(134, $method->invoke($handler, LogLevel::INFO));
        $this->assertEquals(133, $method->invoke($handler, LogLevel::NOTICE));
        $this->assertEquals(132, $method->invoke($handler, LogLevel::WARNING));
        $this->assertEquals(131, $method->invoke($handler, LogLevel::ERROR));
        $this->assertEquals(130, $method->invoke($handler, LogLevel::CRITICAL));
        $this->assertEquals(129, $method->invoke($handler, LogLevel::ALERT));
        $this->assertEquals(128, $method->invoke($handler, LogLevel::EMERGENCY));
    }

    public function testDestruct(): void
    {
        $handler = new SyslogUdpHandler($this->host, $this->port);

        // We can't easily test socket_close, so we'll just ensure no exception is thrown during destruction
        unset($handler);
        $this->assertTrue(true); // If we reach here, no exception was thrown
    }

    public function testHandle(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $formatter = $this->createMock(LogFormatter::class);
        $formatter->method('format')->willReturn('formatted message');

        /** @var SyslogUdpHandler|MockObject */
        $handler = $this->getMockBuilder(SyslogUdpHandler::class)
            ->setConstructorArgs([$this->host, $this->port])
            ->onlyMethods(['sendToSocket'])
            ->getMock();

        $handler->expects($this->once())
            ->method('sendToSocket')
            ->with($this->stringContains('<134>formatted message'))
            ->willReturn(true);

        // Use reflection to set the protected property
        $reflection = new \ReflectionClass($handler);
        $formatterProperty = $reflection->getProperty('formatter');
        $formatterProperty->setAccessible(true);
        $formatterProperty->setValue($handler, $formatter);

        $handler->handle($record);
    }

    public function testHandleThrowsExceptionOnSendFailure(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $formatter = $this->createMock(LogFormatter::class);
        $formatter->method('format')->willReturn('formatted message');

        /** @var SyslogUdpHandler|MockObject */
        $handler = $this->getMockBuilder(SyslogUdpHandler::class)
            ->setConstructorArgs([$this->host, $this->port])
            ->onlyMethods(['sendToSocket'])
            ->getMock();

        $handler->expects($this->once())
            ->method('sendToSocket')
            ->willReturn(false);

        $reflection = new \ReflectionClass($handler);
        $formatterProperty = $reflection->getProperty('formatter');
        $formatterProperty->setAccessible(true);
        $formatterProperty->setValue($handler, $formatter);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Socket sendto failed');

        $handler->handle($record);
    }
}
