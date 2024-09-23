<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Trait;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Trait\LoggerAwareTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LoggerAwareTraitTest extends TestCase
{
    private TestLoggerAware $testObject;
    private Logger|MockObject $loggerMock;

    protected function setUp(): void
    {
        $this->testObject = new TestLoggerAware();
        $this->loggerMock = $this->createMock(Logger::class);
    }

    public function testSetLogger(): void
    {
        $this->testObject->setLogger($this->loggerMock);

        $this->assertSame($this->loggerMock, $this->testObject->getLogger());
    }

    public function testLoggerInitiallyNull(): void
    {
        $this->assertNull($this->testObject->getLogger());
    }

    public function testOverwriteLogger(): void
    {
        /** @var Logger */
        $firstLogger = $this->createMock(Logger::class);
        /** @var Logger */
        $secondLogger = $this->createMock(Logger::class);

        $this->testObject->setLogger($firstLogger);
        $this->assertSame($firstLogger, $this->testObject->getLogger());

        $this->testObject->setLogger($secondLogger);
        $this->assertSame($secondLogger, $this->testObject->getLogger());
    }

    public function testUseLogger(): void
    {
        $message = 'Test message';
        $context = ['key' => 'value'];

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($message, $context);

        $this->testObject->setLogger($this->loggerMock);
        $this->testObject->doSomethingWithLogging($message, $context);
    }

    public function testUseLoggerWhenNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Logger has not been set');

        $this->testObject->doSomethingWithLogging('Test', []);
    }
}

class TestLoggerAware
{
    use LoggerAwareTrait;

    public function getLogger(): ?Logger
    {
        return $this->logger ?? null;
    }

    public function doSomethingWithLogging(string $message, array $context = []): void
    {
        if (!isset($this->logger)) {
            throw new \RuntimeException('Logger has not been set');
        }
        $this->logger->info($message, $context);
    }
}
