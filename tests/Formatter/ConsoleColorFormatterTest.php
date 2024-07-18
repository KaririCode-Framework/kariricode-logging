<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Formatter;

use KaririCode\Logging\Formatter\ConsoleColorFormatter;
use KaririCode\Logging\LogLevel;
use PHPUnit\Framework\TestCase;

class ConsoleColorFormatterTest extends TestCase
{
    private ConsoleColorFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new ConsoleColorFormatter();
    }

    public function testFormat(): void
    {
        $message = 'Test message';
        $formattedMessage = $this->formatter->format(LogLevel::INFO, $message);
        $this->assertStringContainsString("\033[0;32m", $formattedMessage);
        $this->assertStringContainsString($message, $formattedMessage);
        $this->assertStringContainsString("\033[0m", $formattedMessage);
    }
}
