<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Trait;

use KaririCode\Contract\Logging\LogLevel as LoggingLogLevel;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\Trait\LoggerTrait;
use PHPUnit\Framework\TestCase;

final class LoggerTraitTest extends TestCase
{
    private TestLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new TestLogger();
    }

    /**
     * @dataProvider logLevelProvider
     */
    public function testLogMethods(string $method, LoggingLogLevel $expectedLevel): void
    {
        $message = 'Test message';
        $context = ['key' => 'value'];

        $this->logger->$method($message, $context);

        $this->assertEquals([
            'level' => $expectedLevel,
            'message' => $message,
            'context' => $context,
        ], $this->logger->getLastLogEntry());
    }

    public static function logLevelProvider(): array
    {
        return [
            ['emergency', LogLevel::EMERGENCY],
            ['alert', LogLevel::ALERT],
            ['critical', LogLevel::CRITICAL],
            ['error', LogLevel::ERROR],
            ['warning', LogLevel::WARNING],
            ['notice', LogLevel::NOTICE],
            ['info', LogLevel::INFO],
            ['debug', LogLevel::DEBUG],
        ];
    }

    public function testLogWithStringableMessage(): void
    {
        $stringableMessage = new class() implements \Stringable {
            public function __toString(): string
            {
                return 'Stringable message';
            }
        };

        $this->logger->info($stringableMessage);

        $this->assertEquals([
            'level' => LogLevel::INFO,
            'message' => 'Stringable message',
            'context' => [],
        ], $this->logger->getLastLogEntry());
    }

    public function testLogWithEmptyContext(): void
    {
        $this->logger->debug('Debug message');

        $this->assertEquals([
            'level' => LogLevel::DEBUG,
            'message' => 'Debug message',
            'context' => [],
        ], $this->logger->getLastLogEntry());
    }
}

class TestLogger
{
    use LoggerTrait;

    private array $logs = [];

    public function log(LoggingLogLevel $level, \Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [
            'level' => $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }

    public function getLastLogEntry(): ?array
    {
        return end($this->logs) ?: null;
    }
}
